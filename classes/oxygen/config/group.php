<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Config_Group extends Kohana_Config_Group {

	/**
	 * Sets a value in the configuration array.
	 *
	 *     $config->set($key, $new_value);
	 *
	 * @param   string  $key    array key
	 * @param   mixed   $value  array value
	 * @return  Oxygen_Config_Reader
	 */
	public function set($key, $value) {
		$_value = $this->get($key);
		if (is_array($_value)) {
			$value = Arr::merge($_value, $value);
		}

		return parent::set($key, $value);
	}

	/**
	 * Attempts to load the user's custom preference. If one isn't set, then default
	 * to loading the configuration value.
	 *
	 * @param  string  $key         preference key
	 * @param  string  $config_key  config key to fall back to if different than the preference key
	 * @return string|null
	 */
	public function preference($key, $config_key = null) {
		$value = null;
		if (Auth::instance()->logged_in()) {
			$value = Auth::instance()->get_user()->preference($key);
		}

		if ($value === null) {
			if ($config_key !== null) {
				$key = $config_key;
			}

			return $this->get($key);
		}

		return $value;
	}

} // End Oxygen_Config_Group
