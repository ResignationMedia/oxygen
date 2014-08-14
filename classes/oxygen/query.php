<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright  (c) Crowd Favorite. All Rights Reserved.
 * @package    Oxygen
 * @subpackage Core
 */
class Oxygen_Query {

	/**
	 * Initializes the Query.
	 *
	 * @param  mixed   $object
	 * @param  array   $args
	 * @param  array   $order
	 * @return Query
	 */
	public static function factory($object, array $args, $order = null) {
		return new self($object, $args, $order);
	}

	/**
	 * @var  OModel|ORM  object to use to help build the query
	 */
	protected $_object = null;

	/**
	 * @var  array  arguments
	 */
	protected $_args = array();

	/**
	 * @var  array  order
	 */
	protected $_order = null;

	/**
	 * Initializes the Query.
	 *
	 * @param  mixed   $object
	 * @param  array   $args
	 * @param  array   $order
	 */
	public function __construct($object, array $args, $order) {
		if (is_string($object)) {
			$this->_object = OModel::factory($object);
		}
		else {
			$this->_object = $object;
		}

		$this->_args = $args;
		$this->_order = $order;
	}

	/**
	 * Executes the query.
	 *
	 * Example:
	 *
	 *      $created_users = Query::factory('Model_User', array(
	 *          array('created_by', '=', 1),
	 *      ))->execute();
	 *
	 * @param  Pagination  $pagination
	 * @return Database_Result|OModel
	 */
	public function execute(Pagination $pagination = null) {
		$this->_object->where_open();
		$this->compile($this->_args);
		$this->_object->where_close();

		if ($this->_order !== null) {
			$this->_object->order_by($this->_order[0], $this->_order[1]);
		}

		return $this->_object->find_all($pagination);
	}

	/**
	 * Counts all of the objects items.
	 *
	 * @return int
	 */
	public function count_all() {
		$this->_object->where_open();
		$this->compile($this->_args);
		$this->_object->where_close();

		return $this->_object->count_all();
	}

	/**
	 * Compiles the arguments into the executable SQL.
	 *
	 * @param  array   $params
	 *
	 * @return void
	 */
	private function compile(array $params) {
		foreach ($params as $op => $_params) {
			foreach ($_params as $_param) {
				if (isset($_param['or']) || isset($_param['and'])) {
					$this->open($op);
					$this->compile($_param);
					$this->close($op);
				}
				else {
					$columns = $this->_object->list_columns();
					$object_name = $this->_object->object_name().'.'.$_param[0];
					if (!isset($columns[$_param[0]]) && isset($this->_object->$_param[0])) {
						$object_name = $_param[0].'.'.$this->_object->$_param[0]->primary_key();

						// Bind the related object to load with the request.
						$this->_object->with($_param[0]);
					}

					$field = $this->_object->field($_param[0]);
					if ($field !== false) {
						$_param[2] = $field->query_translation($_param[2]);
					}
					else {
						switch ($_param[2]) {
							case 'current_user_id':
								$_param[2] = Auth::instance()->get_user()->id;
							break;
						}
					}

					if ($op == 'and') {
						$this->_object->and_where($object_name, $_param[1], $_param[2]);
					}
					else {
						$this->_object->or_where($object_name, $_param[1], $_param[2]);
					}
				}
			}
		}
	}

	/**
	 * Opens the group.
	 *
	 * @param  string  $op
	 *
	 * @return string
	 */
	private function open($op) {
		if ($op == 'and') {
			$this->_object->and_where_open();
		}
		else {
			$this->_object->or_where_open();
		}
	}

	/**
	 * Closes the group
	 *
	 * @param  string  $op
	 *
	 * @return void
	 */
	private function close($op) {
		if ($op == 'and') {
			$this->_object->and_where_close();
		}
		else {
			$this->_object->or_where_close();
		}
	}

} // End Oxygen_Query
