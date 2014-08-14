<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Controller extends Kohana_Controller {

	/**
	 * Redirects to the install if needed.
	 */
	public function before() {
		// Show installation? (Part 1: no config file)
		if (!file_exists(DOCROOT.'config.php')) {
			if (!in_array($this->request->controller(), array('install', 'resources'))) {
				//$this->request->redirect('install');
			}
			return;
		}

		/**
		 * Load configuration items stored in the database.
		 */
		try {
			$settings = OModel::factory('Setting')->find_all();
			if (count($settings)) {
				foreach ($settings as $key => $value) {
					if (!empty($value)) {
						Oxygen::config('oxygen')->set($key, $value);
					}
				}
			}
		}
		catch (Exception $e) {
			// Settings table may not exist yet, eat the error.
		}

		// Show installation? (Part 2: config file exists)
		$install_date = Oxygen::config('oxygen')->get('application_install_date');
		if ($install_date === null) {
			if (!in_array($this->request->controller(), array('install', 'resources'))) {
				$this->request->redirect('install');
			}
			return;
		}
		else if ($install_date !== null && $this->request->controller() == 'install') {
			$this->request->redirect();
		}

		// Upgrade?
		if (Oxygen::config('oxygen')->get('oxygen_install_version') < Oxygen::config('version.oxygen') ||
			Oxygen::config('oxygen')->get('application_install_version') < Oxygen::config('version.app')) {
			if (!in_array($this->request->controller(), array('upgrade', 'resources'))) {
				$this->request->redirect('upgrade');
			}
		}

		parent::before();
	}

} // End Oxygen_Controller
