<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_History extends Controller_Protected {

	/**
	 * @var  bool  Favorites enabled
	 */
	public $favorites_enabled = false;

	/**
	 * Displays a list of audits.
	 *
	 * [!!] If the table passed in doesn't match the model passed in, an exception will be thrown.
	 *
	 * @throws Oxygen_Access_Exception
	 */
	public function action_index() {
		$table = $this->request->param('table');
		$id = $this->request->param('id');
		$model = $this->request->param('model');

		// Does the user have permission to view the audit history for this class?
		if (!Auth::instance()->has_permission('view', $model)) {
			throw new Oxygen_Access_Exception;
		}

		// Load the model
		$model = OModel::factory($model, $id);

		// Make sure the table name matches
		if ($table != $model->table_name()) {
			throw new Oxygen_Access_Exception;
		}

		// History
		$model_history = $model->history();
		$history = View::factory('audit/list/shell', array(
			'items' => $this->_items($model_history),
			'a' => null,
			'b' => null
		));

		// AJAX?
		if ($this->request->is_ajax()) {
			$this->template->response = array(
				'result' => 'success',
				'html' => (string) $history
			);
		}
		else {
			$this->template->set(array(
				'title' => 'Revision History for '.$model->name(),
				'content' => View::factory('audit/item', array(
				'history' => $history,
					'model' => $model
				))
			));
		}
	}

	/**
	 * Shows a specific audit.
	 *
	 * @throws Kohana_Exception|Oxygen_Access_Exception
	 */
	public function action_view() {
		$audit = OModel::factory('Audit', $this->request->param('id'));
		if (!$audit->loaded()) {
			throw new Kohana_Exception('Page Not Found');
		}

		$model = $audit->get_model($audit->item);
		if (!Auth::instance()->has_permission('view', get_class($model))) {
			throw new Oxygen_Access_Exception;
		}

		// History
		$history = $model->history();
		$history = View::factory('audit/list/shell', array(
			'items' => $this->_items($history),
			'a' => null,
			'b' => null
		));

		// Set template content
		$this->template->set(array(
			'title' => 'Revisions for '.$model->name(),
			'content' => View::factory('audit/view', array(
				'item' => $audit,
				'model' => $model,
				'history' => $history
			))
		));
	}

	/**
	 * Compares two records.
	 *
	 * @throws Kohana_Exception
	 */
	public function action_compare() {
		$errors = false;
		$a = Arr::get($_POST, 'item_a', $this->request->param('a', 0));
		$b = Arr::get($_POST, 'item_b', $this->request->param('b', 0));

		// Either of them null?
		if (!$a && !$b) {
			throw new Kohana_Exception('Unable to compare :a to :b.', array(
				':a' => HTML::chars($a),
				':b' => HTML::chars($b)
			));
		}

		// Same IDs?
		if ($a == $b) {
			$errors = true;
			Msg::add('error', 'Sorry, you cannot compare a revision to itself.');
		}

		// Check for keywords
		$revisions = array(
			'a' => $a,
			'b' => $b
		);
		if (in_array('current', $revisions) && in_array('previous', $revisions)) {
			$errors = true;
			Msg::add('error', 'Sorry, please re-select your revisions and try again.');
		}

		// Check for keys in a and b, if keywords then get IDs for the keywords
		$values = array(
			'current',
			'previous'
		);

		foreach ($revisions as $key => $version) {
			foreach ($values as $value) {
				if ($value == $version) {
					$source = ($key == 'a' ? $b : $a);

					// Load the model
					$audit = OModel::factory('Audit', $source);
					if (!$audit->loaded()) {
						$errors = true;
						Msg::add('error', 'Sorry, please re-select your revisions and try again.');
					}
					$audit_to_compare = OModel::factory('Audit');
					switch ($value) {
						case 'current':
							$audit_to_compare->where('guid', '=', $audit->guid)
								->order_by('created', 'desc')
								->limit(1)
								->find();
							break;
						case 'previous':
							$audit_to_compare->where('guid', '=', $audit->guid)
								->and_where('created', '<', date(
									'Y-m-d H:i:s',
									$audit->created
								))
								->order_by('created', 'desc')
								->limit(1)
								->find();
							break;
					}
					if (!$audit_to_compare->loaded()) {
						$errors = true;
						Msg::add('error', 'Sorry, please re-select your revisions and try again.');
					}
					else {
						$$key = $audit_to_compare->id;
					}
				}
			}
		}

		// Get the items
		$a_model = OModel::factory('Audit', $a);
		$b_model = OModel::factory('Audit', $b);

		// Build the activity items
		$this->_activity($a_model);
		$this->_activity($b_model);

		if ($a_model->guid != $b_model->guid) {
			$errors = true;
			Msg::add('error', 'Sorry, you cannot compare a revisions of different items.');
		}

		// History
		$model = $a_model->get_model($a_model->item);
		$history = $model->history();
		$history = View::factory('audit/list/shell', array(
			'items' => $this->_items($history),
			'a' => $a,
			'b' => $b
		));

		// Set template content
		$this->template->set(array(
			'title' => 'Compare Revisions for '.$model->name(),
			'content' => View::factory('audit/compare', array(
				'errors' => $errors,
				'a' => $a_model,
				'b' => $b_model,
				'model' => $model,
				'history' => $history
			))
		));
	}

	/**
	 * Builds the history items with their activity data.
	 *
	 * @param  ORM  $history    item A history
	 * @param  ORM  $history_b  item B history
	 * @return array
	 */
	private function _items(&$history, &$history_b = null) {
		$items = array();
		$guids = array();
		$history_items = $history->group_by('audit_id')
			->order_by('created', 'desc')
			->with('activity')
			->find_all();

		foreach ($history_items as $item) {
			if ($item->activity->loaded()) {
				$guids[$item->activity->actor] = true;
				$guids[$item->activity->target] = true;
			}
		}

		// Find all the global items
		if (count($guids)) {
			$global_items = OModel::factory('global_item')
				->where('guid', 'IN', array_keys($guids))
				->find_all();
			foreach ($global_items as $item) {
				$guids[$item->guid] = $item->as_array();
			}
		}

		// Loop through the results one more time to override the guid keys
		foreach ($history_items as $item) {
			if ($item->activity->loaded()) {
				$item->activity->actor = $guids[$item->activity->actor];
				$item->activity->target = $guids[$item->activity->target];
				$file = 'activity/'.$item->activity->type.'/'.$item->activity->verb;
				if (Kohana::find_file('views', $file) === false) {
					$file = 'activity/'.$item->activity->verb;
				}
				$item->description = View::factory($file, $item->activity->as_array());
			}

			$items[] = $item;
		}

		// Need to get the activity for history_b?
		if ($history_b !== null) {
			$history_b = $history_b->with('activity')->find();
			if ($history_b->activity->loaded()) {
				$history_b->activity->actor = $guids[$history_b->activity->actor];
				$history_b->activity->target = $guids[$history_b->activity->target];
				$file = 'activity/'.$history_b->activity->type.'/'.$item->activity->verb;
				if (Kohana::find_file('views', $file) === false) {
					$file = 'activity/'.$history_b->activity->verb;
				}
				$history_b->activity = View::factory($file, $history_b->activity->as_array());
			}
			else {
				$history_b->activity = $history_b->description;
			}
		}

		return $items;
	}

	/**
	 * Loads the activity for the model, if it exists.
	 *
	 * @param  OModel  $model  item A model
	 */
	private function _activity(&$model) {
		$model = $model->with('activity');
		if ($model->activity->loaded()) {
			$activity = Request::factory('/activity/find/'.$model->id.'/'.$model->activity->target);
			$model->description = $activity->execute()->response;
		}
		else {
			$model->description = $model->description;
		}
	}

} // End Controller_Oxygen_History
