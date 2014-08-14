<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright  (c) Crowd Favorite. All Rights Reserved.
 * @package    Oxygen
 * @subpackage Controllers
 */
class Controller_Oxygen_Search extends Controller_Protected {

	/**
	 * Core of the search controller.
	 *
	 * @return void
	 */
	public function action_index() {
		$key = $this->request->param('key');
		$terms = Arr::get($_POST, 'terms', Arr::get($_GET, 'q', ''));
		$search = OModel::factory('Search');
		if ($key === null && $this->request->is_ajax()) {
			$response = $search->quick_search($terms);

			// Error?
			$items = array();
			$view_all = '';
			if ($response['result'] == 'success') {
				$items = $response['response']['items'];
				$view_all = URL::site('search?q='.urlencode($response['response']['terms']));
			}
			else {
				$items = $response['response'];
			}

			$this->template->response = array(
				'html' => View::factory('search/ajax/results', array(
					'result' => $response['result'],
					'items' => $items,
					'view_all' => $view_all
				))->render(),
				'key' => Arr::get($_POST, 'key')
			);
		}
		else {
			if (Arr::get($_GET, 'q')) {
				$_POST['terms'] = Arr::get($_GET, 'q');
			}

			if (Arr::get($_POST, 'search')) {
				$search->fields_init();
				$search->set_field_values();
				$fields = $search->fieldgroup('global');
				if ($fields === false) {
					$this->request->redirect('search');
				}

				$key = $search->search_fields($fields);
				$uri = 'search/'.$key;
				$this->request->redirect($uri);
			}

			// Favorite
			$title = 'Search';
			$this->favorites->title($title);

			$grid = $this->action_grid();
			$this->template->set(array(
				'title' => $title,
				'content' => View::factory('search/global', $grid)
			));
		}
	}

	/**
	 * Builds the search grid.
	 *
	 * @return array
	 */
	public function action_grid() {
		$key = $this->request->param('key');

		$search = OModel::factory('Search');
		$search->fields_init();
		$search->set_field_values();

		$fields = $search->fieldgroup('global');
		$list = '';
		if ($key !== null) {
			$fields = $search->search_fields($fields, $key);
			if ($fields === false) {
				$this->request->redirect('search');
			}

			// Show filters?
			if ($fields['show']->value() !== null) {
				$_POST['show'] = $fields['show']->value();
			}

			$results = $search->search($fields);
			$list = OList::factory('search/list')
				->view('row', 'search/list/row')
				->view('row_empty', 'search/list/empty')
				->model($search)
				->items($results['results'])
				->pagination($results['pagination']);
		}
		$form = OForm::factory()
			->fields($fields)
			->button('search',
			OField::factory('submit')
				->model($search)
				->name('search')
				->default_value('Search')
		);

		if ($this->request->is_ajax()) {
			$this->template->response = array(
				'result' => 'success',
				'html' => (string) $list
			);
		}
		else {
			// Set template data
			return OHooks::instance()->filter(get_class($this).'.search.view_data', array(
				'model' => $search,
				'form' => $form,
				'list' => $list
			));
		}
	}

} // End Controller_Oxygen_Search
