<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Upgrade extends Controller_Template {

	/**
	 * @var  string  default template file
	 */
	public $template = 'upgrade/template';

	/**
	 * Redirect to the root if the version is the same.
	 *
	 * @return void
	 */
	public function before() {
		if ($this->request->action() != 'complete' &&
			Oxygen::config('oxygen')->get('application_install_version') == Oxygen::config('version.app') &&
			Oxygen::config('oxygen')->get('oxygen_install_version' == Oxygen::config('version.oxygen'))) {
			$this->request->redirect();
		}

		parent::before();
	}

	/**
	 * Show the upgrade welcome message.
	 *
	 * @return void
	 */
	public function action_index() {
		if (Arr::get($_POST, 'continue')) {
			$this->request->redirect('upgrade/process');
		}

		$this->template->set(array(
			'title' => 'Upgrade Required',
			'login' => true,
			'content' => View::factory('upgrade/welcome', array(
				'app_name' => Oxygen::config('oxygen')->get('app_name')
			))
		));
	}

	/**
	 * Run the upgrade.
	 *
	 * @return void
	 */
	public function action_process() {
		// TODO Put system into "readonly mode" while this runs.
		$config = Oxygen::config('upgrades');
		$settings = OModel::factory('Setting')->find_all();

		// Oxygen Upgrades?
		if (Oxygen::config('oxygen')->get('oxygen_install_version') < Oxygen::config('version.oxygen')) {
			foreach ($config->get('oxygen') as $file) {
				if (file_exists($file)) {
					include $file;
				}
			}

			$settings->oxygen_install_version = Oxygen::config('version.oxygen');
		}

		// Application Upgrades?
		if (Oxygen::config('oxygen')->get('application_install_version') < Oxygen::config('version.app')) {
			$app_updates = $config->get('application');
			error_log(print_r($app_updates, true));
			if (is_array($app_updates)) {
				foreach ($config->get('application') as $file) {
					if (file_exists($file)) {
						error_log("Ran $file");
						include $file;
					}
				}
			}

			$settings->application_install_version = Oxygen::config('version.app');
		}

		$settings->save();

		Session::instance()->set('upgrade_complete', true);
		$this->request->redirect('upgrade/complete');
	}

	/**
	 * Show the complete message.
	 *
	 * @return void
	 */
	public function action_complete() {
		$complete = Session::instance()->get('upgrade_complete');
		if ($complete) {
			Session::instance()->delete('upgrade_complete');
			$this->template->set(array(
				'title' => 'Upgrade Complete',
				'login' => true,
				'content' => View::factory('upgrade/complete', array(
					'app_name' => Oxygen::config('oxygen')->get('app_name'),
				))
			));
		}
		else {
			$this->request->redirect();
		}
	}

} // End Controller_Oxygen_Upgrade
