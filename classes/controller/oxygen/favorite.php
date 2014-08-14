<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Favorite extends Controller_Protected {

	/**
	 * Checks to make sure this is coming from an AJAX request.
	 */
	public function before() {
		if (!$this->request->is_ajax()) {
			$this->request->redirect('/');
		}

		parent::before();
	}

	/**
	 * Add a favorite.
	 */
	public function action_add() {
		$key = Arr::get($_POST, 'key');
		$url = Arr::get($_POST, 'url');
		$title = Arr::get($_POST, 'title');
		if ($url !== null && $title !== null) {
			if ($key === null) {
				$key = '*';
			}

			$url = str_replace(URL::site(), '', $url);
			$bookmark = Bookmark::factory($url, $title);
			$user = Auth::instance()->get_user();

			if ($user->favorite_add($bookmark, $key)) {
				$this->template->response = array(
					'action' => 'favorite/add',
					'result' => 'success',
					'url' => $url,
					'html' => View::factory('user/favorites', array(
						'favorites' => Favorites::load_all($user),
					))->render()
				);
			}
		}
		else {
			// Error...
			$this->template->response = array(
				'result' => 'fail'
			);
		}
	}

	/**
	 * Delete a favorite.
	 */
	public function action_delete() {
		$url = Arr::get($_POST, 'url');
		if ($url !== null) {
			$user = Auth::instance()->get_user();
			if ($user->favorite_delete($url)) {
				$this->template->response = array(
					'action' => 'favorite/delete',
					'result' => 'success',
					'url' => $url,
					'html' => View::factory('user/favorites', array(
						'favorites' => Favorites::load_all($user),
					))->render()
				);
			}
		}
		else {
			// Error...
			$this->template->response = array(
				'result' => 'fail'
			);
		}
	}

} // End Controller_Oxygen_Favorite
