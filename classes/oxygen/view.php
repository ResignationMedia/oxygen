<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
abstract class Oxygen_View extends Kohana_View {

	/**
	 * Log which views are used to render the current page (in the order they are called).
	 *
	 * @param   string  $file   view filename
	 * @return  string
	 * @throws  View_Exception
	 * @uses    View::capture
	 */
	public function render($file = NULL) {
		Oxygen::$view_counter++;
		
		$filename = (!empty($file) ? $file : $this->_file);

		$log_filename = str_replace(array(DOCROOT, '.php'), '', $filename);
		$token = Profiler::start('views', $log_filename.' - '.Oxygen::$view_counter);
		$return = parent::render($file);
		Profiler::stop($token);
		
		return $return;
	}


} // End Oxygen_View
