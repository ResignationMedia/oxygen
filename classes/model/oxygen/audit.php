<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 *
 * @property int $id
 * @property int $item
 * @property int $user_id
 * @property int $created
 * @property string $guid
 * @property string $table
 * @property string $description
 * @property string $data
 * @property string $activity
 */
abstract class Model_Oxygen_Audit extends OModel {

	/**
	 * Audit Constants
	 */
	const AUTO = 1;
	const OFF = 2;
	const FORCE = 3;

	/**
	 * @var  bool  audit status: Off
	 */
	protected $_audit_status = OAudit::OFF;

	/**
	 * @var  int  activity status: Off
	 */
	protected $_activity_status = OActivity::OFF;

	/**
	 * @var  bool  disable global search logging
	 */
	protected $_include_in_global_search = false;

	/**
	 * @var  bool  disable global item logging
	 */
	protected $_create_global_item = false;

	/**
	 * @var  array  meta
	 */
	protected $_meta = array(
		'one' => 'history',
		'mult' => 'history',
		'one_text' => 'History',
		'mult_text' => 'History'
	);

	/**
	 * @var  string  table name
	 */
	protected $_table_name = 'audit';

	/**
	 * @var  array  has one relationship
	 */
	protected $_has_one = array('activity' => array(
		'model' => 'activity',
		'foreign_key' => 'audit_id'
	));

	/**
	 * @var  string  created by column
	 */
	protected $_created_by_column = null;

	/**
	 * @var  string  updated by column
	 */
	protected $_updated_by_column = null;

	/**
	 * @var  array  updated column
	 */
	protected $_updated_column = null;

	/**
	 * @var string obsolete column
	 */
	protected $_obsolete_column = null;

	/**
	 * @var  int  audit limit
	 */
	protected $_audit_limit = 100;

	/**
	 * Returns an array of model permissions.
	 *
	 * @return array
	 */
	public function permissions() {
		return array('system' => array(
			'view',
		));
	}

	/**
	 * List head actions.
	 *
	 * @return array
	 */
	public function list_header_actions() {
		return array();
	}

	/**
	 * Sets the history limit for Audit
	 *
	 * @param  mixed  $id  mixed parameter for find || object to load
	 */
	public function __construct($id = NULL) {
		parent::__construct($id);
		$limit = Oxygen::config('oxygen')->preference('audit_limit');
		if ($limit !== null) {
			$this->_audit_limit = $limit;
		}
	}

	/**
	 * Loads an array of values into into the current object.
	 *
	 * @chainable
	 * @param   array  $values  values to load
	 * @return  ORM
	 */
	protected function _load_values(array $values) {
		if (isset($values['data'])) {
			if (($data = json_decode($values['data'])) != null) {
				$values['data'] = $data;
			}
			else {
				$values['data'] = unserialize($values['data']);
			}

			$data = array();
			foreach ($values['data'] as $key => $value) {
				if (is_string($value)) {
					if (($v = json_decode($value)) !== null) {
						$value = $v;
					}
					else if (($v = @unserialize($value)) !== false) {
						$value = $v;
					}
				}

				$data[$key] = $value;
			}

			if (isset($data['created'])) {
				$data['created'] = strtotime($data['created']);
			}
			if (isset($data['updated'])) {
				$data['updated'] = strtotime($data['updated']);
			}

			$values['data'] = $data;
		}

		parent::_load_values($values);
	}

	/**
	 * Records an audit.
	 *
	 * @param  OModel  $model
	 * @param  string  $action  action type
	 * @param  bool	   $force   force audits
	 * @return bool|OModel
	 */
	public function record(&$model, $action, $force = false) {
		$user = Auth::instance()->get_user();
		if (!$user) {
			$user = User::system();
		}

		// Set data
		$this->guid = Oxygen::guid($model);
		$this->table = $model->table_name();
		$this->item = $model->id;
		$this->user_id = $user->id;
		$this->description = $model->meta('one_text').' "'.$model->name().'" was modified ('.$action.') by '.$user->name();
		$this->data = json_encode($model->audit_data());

		// Only check old records if we're not forcing an audit.
		if (!$force) {
			// Load the last audit
			$audit = OModel::factory('Audit')
				->where('table', '=', $model->table_name())
				->and_where('item', '=', $model->id)
				->order_by('created', 'desc')
				->limit(1)
				->find();

			// Make sure something changed.
			if ($audit->loaded() && $audit->data == $this->data) {
				// Nothing has changed...
				return false;
			}
		}

		// Save
		return $this->save();
	}

	/**
	 * Loads the model for the current Audit item.
	 *
	 * @param  mixed  $id
	 * @return OModel|ORM
	 */
	public function get_model($id = null) {
		$model = OModel::factory(Inflector::singular($this->table), $id);
		$model->init();
		$model->fields_init();
		foreach ($model->table_columns() as $column => $data) {
			if (isset($model->_fields[$column]) && isset($this->data[$column])) {
				$model->_fields[$column]->value($this->data[$column]);
			}
		}
		$model->set_field_values(true);
		$model->friendly_values(true);

		return $model;
	}

	/**
	 * Converts items into a list.
	 *
	 * @static
	 * @param  mixed  $value
	 * @param  bool   $humanize
	 * @return string
	 */
	public static function complex_display($value, $humanize = true) {
		if (is_scalar($value)) {
			// It's already a string, bail out.
			return ($humanize ? Inflector::humanize($value, true) : $value);
		}

		$output = '';
		if (count($value)) {
			$output = '<ul>';
			foreach ($value as $k => $v) {
				$output .= '<li>';
				if (!is_int($k)) {
					$output .= '<strong>'.Inflector::humanize($k, true).'</strong> ';
				}
				$output .= OAudit::complex_display($v).'</li>';
			}
			$output .= '</ul>';
		}

		return $output;
	}

} // End Model_Oxygen_Audit
