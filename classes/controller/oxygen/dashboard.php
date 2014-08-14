<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Dashboard extends Controller_Protected {

	/**
	 * Sets $this->template->nav_current
	 */
	public function before() {
		parent::before();
		$this->template->set('nav_current', 'dashboard');
	}

	/**
	 * Dashboard
	 */
	public function action_index() {
		// Load the activities
		$activities = Request::factory('activity/recent');
		$activities->get = array(
			'limit' => 10
		);
		$activities = $activities->execute();

		// Set template content
		$this->template->set(array(
			'title' => 'Dashboard',
			'content' => View::factory('dashboard/dashboard', array(
				'activities' => $activities
			))
		));
	}

} // End Controller_Oxygen_Dashboard
