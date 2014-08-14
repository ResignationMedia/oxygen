<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 *
 * This class exists to make the Profiler internally aware of whether or not it is enabled
 */
class Oxygen_Profiler extends Kohana_Profiler {

	/**
	 * Starts a new benchmark and returns a unique token. The returned token
	 * _must_ be used when stopping the benchmark.
	 *
	 *     $token = Profiler::start('test', 'profiler');
	 *
	 * @param   string  $group  group name
	 * @param   string  $name   benchmark name
	 * @return  string
	 */
	public static function start($group, $name) {
		if (Oxygen::$profiling) {
			return parent::start($group, $name);
		}
	}

	/**
	 * Stops a benchmark.
	 *
	 *     Profiler::stop($token);
	 *
	 * @param   string  $token
	 * @return  void
	 */
	public static function stop($token) {
		if (Oxygen::$profiling) {
			return parent::stop($token);
		}
	}

}
