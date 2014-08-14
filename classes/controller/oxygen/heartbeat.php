<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Heartbeat extends Controller_Oxygen {

	/**
	 * Checks to see if the system is alive.
	 */
	public function action_key() {
		$key = $this->request->param('key');
		if ($key != Oxygen::config('oxygen')->get('heartbeat_key')) {
			$this->template->response = array(
				'status' => 500,
				'message' => 'Invalid Key - Access Denied'
			);

			return;
		}

		$status = 0;
		$token = Profiler::start('heartbeat', 'test');
		$result = DB::select('id')->from('users')->execute();
		if ($result->count() > 0) {
			$status = 1;
		}
		Profiler::stop($token);

		list($time, $memory) = Profiler::total($token);

		$this->template->response = array(
			'status' => $status,
			'timer' => $time
		);
	}

} // End Controller_Oxygen_Heartbeat
