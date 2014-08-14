<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright  (c) Crowd Favorite. All Rights Reserved.
 * @package    Oxygen
 * @subpackage Models
 *
 * @property string $key
 * @property string $model
 * @property string $params
 */
abstract class Model_Oxygen_Search extends OModel {

	/**
	 * @var  int  audit status: Off
	 */
	protected $_audit_status = OAudit::OFF;

	/**
	 * @var  int  activity status: Off
	 */
	protected $_activity_status = OActivity::OFF;

	/**
	 * @var  bool  log global search
	 */
	protected $_include_in_global_search = false;

	/**
	 * @var  bool  log global item
	 */
	protected $_create_global_item = false;

	/**
	 * @var  string  created by column
	 */
	protected $_created_by_column = null;

	/**
	 * @var  array  created column
	 */
	protected $_created_column = null;

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
	 * Builds the correct URL for the search grid.
	 *
	 * @param  string  $type
	 * @param  array   $uri
	 *
	 * @return string
	 */
	public function url($type = 'edit', array $uri = array()) {
		if ($type == 'grid') {
			$uri = array(
				'search',
				Request::current()->param('key')
			);
		}
		return parent::url($type, $uri);
	}

	/**
	 * Initializes the form fields and fieldgroups.
	 */
	public function fields_init() {
		parent::fields_init();

		// Fields
		$this->_fields += array(
			'terms' => OField::factory()->name('terms')
				->model($this)
				->label('')
				->attributes(array(
				'placeholder' => 'Search'
			)),

			'items_per_page' => OField::factory()->name('items_per_page')
				->model($this)
				->default_value(Oxygen::config('oxygen')->preference('search_items_per_page')),

			'sort' => OField::factory('select')->name('sort')
				->model($this)
				->options(array(
				'title_sort' => 'Title',
				'summary' => 'Summary'
			)),

			'sort_order' => OField::factory('radio')->name('sort_order')
				->model($this)
				->label('')
				->options(array(
				'asc' => 'A-Z',
				'desc' => 'Z-A'
			))
				->default_value('asc'),

			'show' => OField::factory('checkbox')->name('show')
				->model($this)
				->label('Show:')
				->options(array(
				'title' => 'Title',
				'description' => 'Description',
				'created' => 'Date Created',
				'updated' => 'Date Modified'
			)),

			'filter' => OField::factory('checkbox')->name('filter')
				->model($this)
				->label('Filter:')
		);

		// Fieldgroups
		$this->_fieldgroups += array(
			'global' => array(
				'terms',
				'items_per_page',
				'sort',
				'sort_order',
				'show',
				'filter'
			)
		);
	}

	/**
	 * Ignores the defined models for global search filtering.
	 *
	 * @return array
	 */
	public function ignored_filters() {
		return array(
			'audit', 'media', 'permission', 'preference', 'search', 'setting'
		);
	}

	/**
	 * Sets the field values.
	 *
	 * @param  bool   $ignore_post  ignore $_POST values?
	 * @param  array  $fields       fields to set values for
	 */
	public function set_field_values($ignore_post = false, $fields = null) {
		parent::set_field_values($ignore_post, $fields);

		if (isset($this->_fields['filter'])) {
			// Active models
			$filters = Oxygen::config('oxygen')->get('active_models', array());
			foreach ($filters as $filter => $label) {
				if (in_array($filter, $this->ignored_filters())) {
					unset($filters[$filter]);
				}
			}

			$this->_fields['filter']->options($filters);
		}
	}

	/**
	 * Generates an md5 string to use to find previous searches.
	 *
	 * @chainable
	 *
	 * @param  array  $values    Array of column => val
	 * @param  array  $expected  Array of keys to take from $values
	 *
	 * @return ORM
	 */
	public function values(array $values, array $expected = null) {
		parent::values($values, $expected);
		return $this;
	}

	/**
	 * Assigns the key.
	 *
	 * @param  Validation  $validation  Validation object.
	 *
	 * @return ORM
	 */
	public function create(Validation $validation = NULL) {
		$this->key = md5($this->params.$this->params.$this->model);
		return parent::create($validation);
	}

	/**
	 * Performs a quick search.
	 *
	 * @param  string  $terms
	 *
	 * @return array
	 */
	public function quick_search($terms) {
		$terms = trim($terms);
		$terms = str_replace('*', '%', $terms);

		if (empty($terms)) {
			return array(
				'result' => 'error',
				'response' => 'Please enter a search term.'
			);
		}

		$classes = Auth::instance()->accessible_classes();
		if (!count($classes)) {
			return array(
				'result' => 'error',
				'response' => 'No matches found.'
			);
		}

		$limit = Oxygen::config('oxygen')->preference('quick_search_limit');
		$results = OModel::factory('global_search')
			->where('type', 'IN', $classes)
			->where('content', 'LIKE', '%'.$terms.'%')
			->limit($limit)
			->order_by('title_sort', 'ASC')
			->find_all();

		if (!$results->count()) {
			return array(
				'result' => 'error',
				'response' => 'No matches found.'
			);
		}

		$items = array();
		foreach ($results as $result) {
			$items[$result->id] = $result;
		}

		// Fill the data with more content?
		$total = count($items);
		if ($total < $limit) {
			$results = OModel::factory('Global_Search')
				->where('content', 'LIKE', '%'.$terms.'%')
				->limit(($limit - $total))
				->order_by('updated', 'DESC');

			// Filter out types
			if ($total) {
				$types = array();
				foreach ($items as $item) {
					$types[] = $item->type;
				}
				$results->where('type', 'NOT IN', $types);
			}

			$results = $results->find_all();

			if ($results->count()) {
				foreach ($results as $result) {
					$items[$result->id] = $result;
				}
			}
		}

		return array(
			'result' => 'success',
			'response' => array(
				'items' => $items,
				'terms' => $terms
			)
		);
	}

	/**
	 * Performs a global search
	 *
	 * @param  array  $fields
	 *
	 * @return array
	 */
	public function search(array $fields) {
		// Terms
		$model = OModel::factory('Global_Search')
			->where('content', 'LIKE', '%'.$fields['terms']->value().'%');

		// Filters
		$filters = $fields['filter']->value();
		if (!empty($filters)) {
			$model->where_open();
			foreach ($filters as $filter) {
				$model->or_where('type', '=', 'Model_'.$filter);
			}
			$model->where_close();
		}

		$items_per_page = Oxygen::config('oxygen')->preference('search_items_per_page');
		if (isset($fields['items_per_page']) && $fields['items_per_page']->value() <= $items_per_page) {
			$value = $fields['items_per_page']->value();
			if (!empty($value)) {
				$items_per_page = $value;
			}
		}
		$total = clone $model;
		$total = $total->count_all();
		$pagination = Pagination::factory(array(
			'items_per_page' => $items_per_page,
			'total_items' => $total
		));
		$total = null;

		// Order by
		if ($fields['sort']->value() !== null) {
			$model->order_by($fields['sort']->value(), $fields['sort_order']->value());
		}

		return array(
			'results' => $model->find_all($pagination)->as_array(),
			'pagination' => $pagination
		);
	}

} // End Model_Oxygen_Search
