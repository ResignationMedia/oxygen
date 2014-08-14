<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Test extends Controller_Oxygen {

	/**
	 * Block access to this controller in production.
	 */
	public function before() {
		if (Oxygen::$environment === Oxygen::PRODUCTION) {
			$this->request->redirect('/');
		}

		parent::before();
	}

    /**
     * A page the renders all of the form elements. This is just a sanity check to make sure elements render
	 * correctly.
     */
    public function action_form() {
		$this->template->set(array(
			'title' => 'Form Element Rendering',
			'content' => View::factory('test/form')
		));
    }

} // End Controller_Oxygen_Test
