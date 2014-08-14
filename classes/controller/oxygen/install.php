<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package oxygen
 * @subpackage controllers
 *
 * @property Request $request
 */
class Controller_Oxygen_Install extends Controller_Template {

	/**
	 * @var  string  default template file
	 */
	public $template = 'install/template';

	/**
	 * This is the order in which the installation will execute actions.
	 *
	 * @return array
	 */
	protected function order() {
		return array(
			'config_check',
			'process',
			'create_admin',
		);
	}

	/**
	 * Determines what action to run.
	 *
	 * @return void
	 */
	public function before() {
		parent::before();

		$key = $this->request->param('key');
		if ($key !== null) {
			$this->request->action($key);
		}
	}

	/**
	 * Finds the next page key to go to.
	 *
	 * @param  string  $key
	 * @return string
	 */
	protected function get_next_key($key) {
		$keys = $this->order();
		$i = array_search($key, $keys);

		// Is there a next step?
		$x = $i + 1;
		if (isset($keys[$x])) {
			return $keys[$x];
		}
		else {
			return 'complete';
		}
	}

	/**
	 * Run the installation.
	 *
	 * @return void
	 */
	public function action_index() {
		if (!file_exists(DOCROOT.'config.sample.php')) {
			throw new Oxygen_Install_Exception('Missing the config.sample.php. Please re-upload this file from your :app_name installation.',
				array(':app_name' => Oxygen::config('oxygen')->get('app_name')));
		}

		if (Arr::get($_POST, 'next')) {
			$keys = $this->order();
			$this->request->redirect('install/'.$keys[0]);
		}

		$app_name = Oxygen::config('oxygen')->get('app_name');
		$this->template->set(array(
			'title' => 'Welcome to '.$app_name,
			'login' => true,
			'content' => View::factory('install/welcome', array(
				'app_name' => $app_name
			)),
		));
	}

	/**
	 * Checks the system configuration.
	 *
	 * @throws Oxygen_Install_Exception
	 * @return void
	 */
	public function action_config_check() {
		// Check the configuration file if it exists.
		if (file_exists(DOCROOT.'config.php')) {
			try {
				DB::query(Database::SELECT, 'SHOW TABLES')->execute();
				$this->request->redirect('install/'.$this->get_next_key('config_check'));
			}
			catch (Exception $e) {
				throw new Oxygen_Install_Exception('Failed to connect to the database. Please check your connection settings in config.php.');
			}
		}

		$writeable = true;
		$post = array();
		$content = null;
		if (Arr::get($_POST, 'next')) {
			$post = Validation::factory($_POST);
			foreach ($this->rules() as $field => $rules) {
				$post->rules($field, $rules);
			}

			if ($post->check()) {
				// Build the configuration file
				$file = file(DOCROOT.'config.sample.php');
				foreach ($file as $i => $line) {
					if (substr($line, 0, 17) == '$config[\'domain\']') {
						$file[$i] = str_replace('localhost', $post['domain'], $line);
					}
					else if (substr($line, 0, 3) == '$db') {
						switch (substr($line, 0, 15)) {
							case '$db[\'hostname\']':
								$file[$i] = str_replace('localhost', $post['hostname'], $line);
							break;
							case '$db[\'username\']':
								$file[$i] = str_replace('USERNAME\';', $post['username'].'\';', $line);
							break;
							case '$db[\'password\']':
								$file[$i] = str_replace('PASSWORD\';', $post['password'].'\';', $line);
							break;
							case '$db[\'database\']':
								$file[$i] = str_replace('oxygen', $post['database'], $line);
							break;
							case '$db[\'table_pref':
								$file[$i] = str_replace('o_', $post['table_prefix'], $line);
							break;
						}
					}
				}

				if (Arr::get($_POST, 'file_content') || !is_writeable(DOCROOT)) {
					$writeable = false;
					if (Arr::get($_POST, 'file_content')) {
						Msg::add('error', 'Please paste the following content into your created config.php.');
					}

					$content = $this->additional_config($file);
				}
				else {
					$handle = fopen(DOCROOT.'config.php', 'w');
					foreach($file as $line ) {
						fwrite($handle, $line);
					}
					fclose($handle);
					chmod(DOCROOT.'config.php', 0666);
					$this->request->redirect('install/'.$this->get_next_key('config_check'));
				}
			}
			else {
				foreach ($post->errors('validation/install') as $error_text) {
					Msg::add('error', $error_text);
				}
			}
		}

		$messages = View::factory('chrome/messages');

		if ($writeable && !file_exists(DOCROOT.'config.php')) {
			$title = 'Database Configuration';
			$content = View::factory('install/database', array(
				'messages' => $messages
			));
		}
		else {
			$title = 'Configuration File';
			$content = View::factory('install/file', array(
				'messages' => $messages,
				'config_items' => $post,
				'content' => $content
			));
		}

		$this->template->set(array(
			'title' => $title,
			'login' => true,
			'content' => $content,
		));
	}

	/**
	 * Processes the installation scripts.
	 *
	 * @throws Oxygen_Install_Exception
	 * @return void
	 */
	public function action_process() {
		$this->nuke_tables();
		$groups = Oxygen::config('install');
		try {
			$upgrade = Oxygen::config('upgrades');

			$this->install_group($groups['oxygen']);
			if (isset($upgrade['oxygen'])) {
				foreach ($upgrade['oxygen'] as $file) {
					include $file;
				}
			}

			if (isset($groups['application'])) {
				$this->install_group($groups['application']);
				if (isset($upgrade['application'])) {
					foreach ($upgrade['application'] as $file) {
						include $file;
					}
				}
			}

			$this->request->redirect('install/'.$this->get_next_key('process'));
		}
		catch (Exception $e) {
			$this->nuke_tables();

			throw new Oxygen_Install_Exception('Failed to install :app_name. Please check your config.php settings and re-run the installation.<br /><br /><p>:link</p>', array(
				':app_name' => Oxygen::config('oxygen')->get('app_name'),
				':link' => HTML::anchor('install', 'Restart Installation')
			));
		}
	}

	/**
	 * Creates the administrator account.
	 *
	 * @return void
	 */
	public function action_create_admin() {
		if (Arr::get($_POST, 'next')) {
			try {

				$user = OModel::factory('User');
				$user->name = Arr::get($_POST, 'name');
				$user->email = Arr::get($_POST, 'email');
				$user->username = Arr::get($_POST, 'username');
				$user->password = Arr::get($_POST, 'password');

				$external_values = array(
					'password' => Arr::get($_POST, 'password'),
					'password_confirm' => Arr::get($_POST, 'password_confirm')
				);
				$extra = Validation::factory($external_values)
					->rule('password', 'not_empty')
					->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));

				$user->create_admin($extra);
				$user->preference('timezone', Oxygen::config('oxygen')->get('timezone', 'America/Denver'));

				Session::instance()->set('admin_id', $user->pk());
				$this->request->redirect('install/'.$this->get_next_key('create_admin'));
			}
			catch (ORM_Validation_Exception $e) {
				foreach ($e->errors('validation') as $error) {
					Msg::add('error', $error);
				}
			}
		}

		$this->template->set(array(
			'title' => 'Create Administrator',
			'login' => true,
			'content' => View::factory('install/admin', array(
				'messages' => View::factory('chrome/messages'),
			)),
		));
	}

	/**
	 * Shows the installation complete view.
	 *
	 * @return void
	 */
	public function action_complete() {
		// Set the installation date
		$setting = OModel::factory('Setting');

		$setting->set('key', 'oxygen_install_version')
			->set('value', Oxygen::config('version.oxygen'))
			->create()
			->clear();

		$setting->set('key', 'application_install_date')
			->set('value', date(Date::$timestamp_format))
			->create()
			->clear();

		$setting->set('key', 'application_install_version')
			->set('value', Oxygen::config('version.app'))
			->create()
			->clear();

		$user = OModel::factory('User', 1);
		$this->template->set(array(
			'title' => 'Installation Complete!',
			'login' => true,
			'content' => View::factory('install/complete', array(
				'user' => $user
			)),
			'complete' => true
		));
	}

	/**
	 * Default installation rules.
	 *
	 * @return array
	 */
	protected function rules() {
		return array(
			'domain' => array(
				array('not_empty'),
			),
			'username' => array(
				array('not_empty'),
			),
			'password' => array(
				array('not_empty')
			),
			'database' => array(
				array('not_empty'),
				array(array($this, 'verify_db'), array(':validation'))
			),
			'table_prefix' => array(
				array('Valid::alpha_dash', array(':field', true))
			)
		);
	}

	/**
	 * Verifies the connection to the database.
	 *
	 * @param  Validation  $post  Validation object
	 * @return bool
	 */
	public function verify_db(Validation $post) {
		$config = Oxygen::config('database')->get('default');
		$config = Arr::merge($config, array(
			'connection' => array(
				'hostname' => $post['hostname'],
				'database' => $post['database'],
				'username' => $post['username'],
				'password' => $post['password'],
			),
			'table_prefix' => $post['table_prefix']
		));

		$db = Database::instance('test', $config);

		try {
			$db->query(Database::SELECT, 'SHOW TABLES');
		}
		catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Override this method to add additional configuration values to the config.php.
	 *
	 * @param  array  $file
	 * @return array
	 */
	protected function additional_config(array $file) {
		return $file;
	}

	/**
	 * Runs the group of SQL files.
	 *
	 * @param  array  $files
	 */
	protected function install_group(array $files) {
		foreach ($files as $file) {
			$queries = include $file;

			foreach ($queries as $query) {
				$query = str_replace('{TABLE_PREFIX}', Oxygen::config('database.default.table_prefix'), $query);
				DB::query(Database::INSERT, $query)->execute('default');
			}
		}
	}

	/**
	 * Nukes the installed tables.
	 */
	private function nuke_tables() {
		// Error occurred, rollback and tables that were created then show an error.
		$results = DB::query(Database::SELECT, 'SHOW TABLES')->execute('default');
		foreach ($results as $result) {
			foreach ($result as $table) {
				DB::query(Database::DELETE, 'DROP TABLE :table')
					->param(':table', DB::expr($table))
					->execute('default');
			}
		}
	}

} // End Controller_Oxygen_Install
