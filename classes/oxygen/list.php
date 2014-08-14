<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_List extends Oxygen_HTML_Element {

	/**
	 * @var  array  default views
	 */
	protected $_views = array(
		'shell' => 'list/shell',
		'actions' => 'list/actions',
		'header' => 'list/header',
		'footer' => 'list/footer',
		'row_header' => 'list/row/header',
		'row' => 'list/row/item',
		'row_empty' => 'list/row/empty',
		'new' => 'list/actions/new',
		'sort' => 'list/actions/sort',
	);

	/**
	 * @var  string  title
	 */
	protected $_title = '';

	/**
	 * @var  array  items
	 */
	protected $_items = array();

	/**
	 * @var  object  Model object
	 */
	protected $_model = null;

	/**
	 * @var  Pagination  object
	 */
	protected $_pagination = null;

	/**
	 * @var  string  custom source url
	 */
	protected $_source_url = null;

	/**
	 * @var  string  custom columns
	 */
	protected $_columns = null;

	/**
	 * Creates an OList object.
	 *
	 * @static
	 * @param  object  $file  model being listed
	 * @param  array   $data  data to be passed into the list view
	 * @return OList
	 */
	public static function factory($file = null, array $data = null) {
		return new OList($file, $data);
	}

	/**
	 * Magic method to force $this->compile() when calling echo.
	 *
	 * @return View
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * Compiles the list before rendering.
	 *
	 * @param  string  $file  shell view file name
	 * @return string
	 */
	public function render($file = null) {
		$this->compile();
		return parent::render($file);
	}

	/**
	 * Adds a model to the list object.
	 *
	 * @chainable
	 * @param  object  $model  model object
	 * @return OList
	 */
	public function model(&$model) {
		$this->_model = $model;
		return $this;
	}

	/**
	 * Set the list title.
	 *
	 * @chainable
	 * @param  string  $title  the list title
	 * @return OList
	 */
	public function title($title = null) {
		if ($title === null) {
			return $this->_title;
		}

		$this->_title = $title;
		return $this;
	}

	/**
	 * Adds items to the list.
	 *
	 * @chainable
	 * @param  array  $items  items being listed
	 * @return OList
	 */
	public function items(array $items = null) {
		if ($items === null) {
			return $this->_items;
		}

		$this->_items = $items;
		return $this;
	}

	/**
	 * Sets the pagination object.
	 *
	 * @chainable
	 * @param  Pagination  $pagination  the pagination object
	 * @return OList|Pagination|string
	 */
	public function pagination($pagination = null) {
		if ($pagination === null) {
			if ($this->_pagination instanceof Pagination) {
				return $this->_pagination;
			}
			else {
				return '';
			}
		}

		$this->_pagination = $pagination;
		return $this;
	}

	/**
	 * Sets the source URL.
	 *
	 * @param  string  $url
	 * @return OList|string
	 */
	public function source_url($url = null) {
		if ($url === null) {
			return $this->_source_url;
		}

		$this->_source_url = $url;
		return $this;
	}

	/**
	 * Sets the columns.
	 *
	 * @param  string  $url
	 * @return OList|string
	 */
	public function columns($columns = null) {
		if ($columns === null) {
			return $this->_columns;
		}

		$this->_columns = $columns;
		return $this;
	}

	/**
	 * Builds the list output
	 *
	 * @return View
	 */
	protected function compile() {
		$this->_model->fields_init();

		// Rows
		$row_header = '';
		$actions = OHooks::instance()->filter($this->_model->object_name().'_list_actions', $this->_model->list_actions());
		$columns = $this->columns();
		if (!$columns) {
			$columns = $this->_model->columns('list');
		}

		if (!empty($columns)) {
			$row_header = $this->load_view('row_header', array(
				'actions' => $actions,
				'columns' => $columns,
				'model' => $this->_model
			));
		}

		$content = '';
		$total = count($this->_items);
		if ($total) {
			$i = 1;
			foreach ($this->_items as $item) {
				if (is_object($item)) {
					$item->fields_init();
					$item->set_field_values(true);
					$item->friendly_values(true);
				}

				$content .= $this->load_view('row', array(
					'item' => $item,
					'actions' => $actions,
					'model' => $this->_model,
					'is_first_item' => ($i === 1),
					'is_last_item' => ($i === $total)
				));

				++$i;
			}
		}
		else {
			$content .= $this->load_view('row_empty', array(
				'colspan' => count($this->_model->list_actions())+count($columns)
			));
		}

		// Load the filter
		$filter = '';
		if ($this->_model->meta('one') !== 'history') {
			//$filter = Request::factory('/search/form/'.$this->_model->meta('one'))->execute()->body();
		}

		// Source URL
		$source_url = $this->source_url();
		if ($source_url === null) {
			$source_url = $this->_model->url('grid');
		}

		// Return the shell
		$this->add_css_class(array('box', 'oxygen-grid'));
		$header_actions = OHooks::instance()->filter($this->_model->object_name().'_list_header_actions', $this->_model->list_header_actions());
		return $this->set(array(
			'model' => $this->_model,
			'attributes' => Arr::merge($this->_attributes, array(
				'id' => $this->_model->meta('mult').'-grid',
				'data-url' => $source_url,
				'data-sort' => Request::current()->param('sort', '-'),
				'data-page' => Request::current()->param('page', 1),
				'data-order' => Request::current()->param('order', '')
			)),
			'header' => $this->load_view('header', array(
				'list' => $this,
				'title' => $this->title(),
				'actions' => $this->load_view('actions', array(
					'actions' => $header_actions,
					'model' => $this->_model,
				)),
				'filter' => $filter,
				'model' => $this->_model,
				'pagination' => $this->pagination(),
			)),
			'total' => $total,
			'row_header' => $row_header,
			'content' => $content,
			'items' => $this->_items,
			'pagination' => $this->pagination(),
			'footer' => $this->load_view('footer', array(
				'model' => $this->_model,
				'pagination' => $this->pagination(),
			))
		));
	}

} // End Oxygen_List
