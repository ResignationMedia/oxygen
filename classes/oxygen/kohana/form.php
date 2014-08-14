<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Kohana_Form extends Kohana_Form {

	/**
	 * Checks to see if a radio/checkbox should be checked.
	 *
	 * @static
	 * @param  string  $field	name of the field
	 * @param  string  $value	value of the field on page load
	 * @param  string  $default  default value of the field
	 * @param  bool	$echo	 echo HTML, or return a boolean?
	 * @return bool|void
	 */
	public static function checked($field, $value, $default = '', $echo = false) {
		$post = Arr::get($_POST, $field);
		if (!empty($post)) {
			if ($post == $value) {
				if ($echo) {
					echo 'checked="checked"';
				}
				else {
					return true;
				}
			}
		}
		else {
			if ($value == $default) {
				if ($echo) {
					echo 'checked="checked"';
				}
				else {
					return true;
				}
			}
		}

		if (!$echo) {
			return false;
		}
	}

	/**
	 * Looks for an action for the model.
	 *
	 * @static
	 * @param  string  $action
	 * @param  OModel  $model
	 * @return void
	 */
	public static function action($action, $model) {
		$action = str_replace('_', '/', $action);
		$path = 'form/actions/'.$action;
		$model_path = str_replace('_', '/', $model->meta('one'));
		if (Kohana::find_file('views/'.$model_path, $path)) {
			$path = $model_path.'/form/actions/'.$action;
		}

		echo View::factory($path, array('model' => $model));
	}

} // End Oxygen_Kohana_Form
