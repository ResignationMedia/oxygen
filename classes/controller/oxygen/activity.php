<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Activity extends Controller {

	/**
	 * Finds all the recent activity items to display.
	 */
	public function action_recent() {
		// Request parameters
		$format = $this->request->param('format', 'html');
		$limit = Arr::get($_GET, 'limit', 10);

		// Start compiling the items
		$items = array();
		foreach (OActivity::stream($limit, $this->request->query('object')) as $item) {
			$model = substr(str_replace('Model_', '', Text::alpha($item['target']['guid'], '_')), 0, -1);
			$model = OModel::factory($model);

			$views = $model->view('activity');
			$type = str_replace('_', '/', $item['type']);
			if ($views !== null && isset($views[$item['type']]) && isset($views[$item['type']][$item['verb']])) {
				$item['view'] = $views[$item['type']][$item['verb']];
			}
			else if (Kohana::find_file('views', 'activity/'.$type.'/'.$item['verb']) !== false) {
				$item['view'] = 'activity/'.$type.'/'.$item['verb'];
			}
			else if (Kohana::find_file('views', 'activity/'.$item['verb']) !== false) {
				$item['view'] = 'activity/'.$item['verb'];
			}
			else {
				if ($format == 'html') {
					throw new Kohana_View_Exception('Could not load view for activity :verb for :type.', array(
						':verb' => $item['verb'],
						':type' => $item['type']
					));
				}
				else {
					$item['view'] = false;
				}
			}

			// Load the actor object
			$actor = OModel::factory('User', $item['actor']['id']);
			$item['actor'] += array(
				'avatar' => $actor->profile_photo('thumbnail'),
				'avatar_url' => $actor->profile_photo_url('thumbnail'),
				'object' => $actor,
			);

			// Load the target object
			$target = OModel::factory($item['type'], $item['target']['id'])->find();
			$item['target']['object'] = $target;

			// Load the view
			$items[] = $item;

			// Remove the instance
			unset($model);
		}

		$body = '';
		if ($format == 'html') {
			if ($this->request->query('object') !== null) {
				$body = OActivity::as_list($items);
			}
			else {
				$body = View::factory('activity/shell', array(
					'items' => $items
				))->render();
			}
		}
		else if ($format == 'json') {
			$body = json_encode($items);
		}

		// Set the response body
		$this->response->body($body);
	}

	/**
	 * Finds a specific activity item by the target and created timestamp.
	 */
	public function action_find() {
		// Request parameters
		$audit_id = $this->request->param('audit_id');
		$target = $this->request->param('target');

		// Find the activity
		$activity = OModel::factory('activity')
			->where('audit_id', '=', $audit_id)
			->and_where('target', '=', $target)
			->find();

		if (!$activity->loaded()) {
			$this->response->body('');
			return;
		}

		// Find the global items
		$data = array(
			'type' => $activity->type,
			'verb' => $activity->verb
		);
		$global = OModel::factory('global_item')
			->where('guid', '=', $activity->actor)
			->or_where('guid', '=', $activity->target)
			->find_all();
		foreach ($global as $item) {
			if ($global->count() == 1 || $item->guid == $target) {
				$data['target'] = $item->as_array();
			}

			if ($item->guid != $target) {
				$data['actor'] = $item->as_array();
			}
		}

		// Set template content
		$body = View::factory('activity/'.$activity->type.'/'.$activity->verb, $data)->render();
		$this->response->body($body);
	}

} // End Controller_Oxygen_Activity
