<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package oxygen
 * @subpackage controllers
 *
 * @property Model_Role $_model
 */
class Controller_Oxygen_Roles extends Controller_CRUD {

	/**
	 * @var  string  model name
	 */
	protected $_model_name = 'Role';

	/**
	 * Initializes the model.
	 */
	public function before() {
		parent::before();

		$ignored_roles = OHooks::instance()->filter('controller_roles_roles_to_ignore', array('1'));

		// Can't edit the super admin role...
		$id = $this->request->param('id');
		if (in_array($id, $ignored_roles)) {
			$this->request->redirect('roles');
		}

		$this->_init_model();
		// Load the model
		$this->_model = OModel::factory($this->_model_name, $id)
			->where(
				$this->_model->object_name().'.'.$this->_model->primary_key(),
				'NOT IN',
				$ignored_roles
			);
	}

	/**
	 * Adds the permission_form hook.
	 */
	public function action_add() {
		OHooks::instance()
			->add(get_class($this).'.add.view_data', array(
				get_class($this),
				'permission_form'
			))
			->add(get_class($this).'.add.model_pre_save', array(
				get_class($this),
				'set_permissions_from_post'
			));

		parent::action_add();
	}

	/**
	 * Adds the permission_form and example_users hooks.
	 */
	public function action_edit() {
		OHooks::instance()
			->add(get_class($this).'.edit.view_data', array(
				get_class($this),
				'permission_form'
			))
			->add(get_class($this).'.edit.view_data', array(
				get_class($this),
				'example_users'
			))
			->add(get_class($this).'.edit.model_pre_save', array(
				get_class($this),
				'set_permissions_from_post'
			));

		parent::action_edit();
	}

	public function action_view() {
		if ($this->_model->is_obsolete()) {
			$this->request->redirect($this->_model->url('reinstate'));
		}
		$this->request->redirect($this->_model->url('edit'));
	}

	/**
	 * Hook Methods
	 * ------------
	 *
	 * These methods can be used when adding Hooks with OHooks::instance()->add_listener().
	 */

	/**
	 * Adds the permissions to the the form object.
	 *
	 * @static
	 * @param  array  $data  data to be modified
	 * @return array
	 */
	public static function permission_form(array $data) {
		extract($data);
		if (!empty($model->id) && $model->id == Oxygen::config('oxygen')->get('default_role')) {
			Msg::add('info',
				__('This is the default role for new users. To delete this role, please set a different role in <a href=":url">Settings</a>.',
					array(
						':url' => URL::site('settings'),
					)
				)
			);
		}
		$groups = OPermissions::instance()->checkboxes($model->permissions, null, true);
		foreach ($groups as $key => $group) {
			if (count($group['fieldsets'])) {
				$form->content('group_'.$key, View::factory('role/groups/group', array(
					'key' => $key,
					'title' => $group['display'],
					'fieldsets' => $group['fieldsets'],
				)));
			}
		}

		$data['form'] = $form;
		return $data;
	}

	/**
	 * Populates the permissions for the object from post.
	 *
	 * @static
	 * @param  Model_Role  $model  model to be modified
	 * @return Model_Role
	 */
	public static function set_permissions_from_post($model) {
		if (isset($_POST['permissions'])) {
			$model->set('permissions', OPermissions::instance()->tier($_POST['permissions']));
		}
		return $model;
	}

	/**
	 * Adds example users to the form object.
	 *
	 * @static
	 * @param  array  $data  data to be modified
	 * @return array
	 */
	public static function example_users(array $data) {
		extract($data);
		if (empty($model->id)) {
			return $data;
		}

		$total = DB::select(array(DB::expr('COUNT(id)'), 'total'))
			->from('users')
			->where('role_id', '=', $model->id)
			->execute()
			->get('total');

		// Load up to 4 example users
		$users = OModel::factory('User')->where('role_id', '=', $model->id)->limit(4)->find_all();
		$total_users = count($users);

		$links = array();
		if ($total_users) {
			foreach ($users as $user) {
				$links[] = $user->link('view');
			}

			if ($total_users == 1) {
				$message = 'There is 1 user with this role: '.$links[0];
			}
			else {
				$message = 'There are '.$total.' users with this role, including: '.implode(', ', $links);
				if (count($links) < $total) {
					$message .= '&hellip;';
				}
			}
		}
		else {
			$message = 'There are no current users with this role.';
		}

		$message = '<p class="mar-top-single">'.$message.'</p>';
		$form->content('roles_message', $message, 'message', 'top');

		$data['form'] = $form;

		return $data;
	}

} // End Controller_Oxygen_Roles
