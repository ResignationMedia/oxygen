<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Activity {

	/**
	 * OActivity Constants
	 */
	const AUTO = 1;
	const OFF = 2;

	/**
	 * @var  ORM  activity object
	 */
	protected $_activity;

	/**
	 * @var  string  target
	 */
	protected $_target = '';

	/**
	 * @var  string  type
	 */
	protected $_type = '';

	/**
	 * Initializes a new OActivity object.
	 *
	 * @static
	 * @param  mixed   $class  model the activity is associated to
	 * @param  string  $type   activity type
	 * @return OActivity
	 */
	public static function factory(&$class, $type = null) {
		return new OActivity($class, $type);
	}

	/**
	 * Initializes a new OActivity object.
	 *
	 * @static
	 * @param  mixed   $class  model the activity is associated to
	 * @param  string  $type   activity type
	 */
	public function __construct(&$class, $type = null) {
		// Load the model
		$this->_activity = ORM::factory('activity');

		// Set the target
		$this->_target = Oxygen::guid($class);

		// Activity type defined?
		if ($type === null) {
			// Default to the model name
			$type = strtolower(str_replace('Model_', '', get_class($class)));
		}

		// Does this activity have a different alias?
		$alias = Oxygen::config('activity.types.'.$type);
		if ($alias !== null) {
			$type = $alias;
		}

		// Set the type
		$this->_type = $type;
	}

	/**
	 * Saves the activity item.
	 *
	 * @param  string  $verb      verb
	 * @param  int     $audit_id  audit id
	 * @param  OModel  $target    real target, pre-defined target will become the destination
	 * @return ORM
	 */
	public function save($verb = 'post', $audit_id = 0, &$target = null) {
		$user = Auth::instance()->get_user();
		if ($user !== false) {
			$this->_activity->audit_id = $audit_id;
			$this->_activity->object = $this->_activity->actor = Oxygen::guid($user);
			if ($target == null) {
				$this->_activity->target = $this->_target;
			}
			else {
				$this->_activity->target = Oxygen::guid($target);
				$this->_activity->destination = $this->_target;
			}
			$this->_activity->type = $this->_type;
			$this->_activity->verb = $verb;
			$this->_activity->created = time();

			$activity = $this->_activity;
			$original = clone $activity;
			$original->create();

			// Create additional rows.
			$target = clone $activity;
			$target->object = $target->target;
			$target->create();
			unset($target);

			if ($activity->destination != null) {
				$destination = clone $activity;
				$destination->object = $destination->destination;
				$destination->create();
				unset($destination);
			}

			return $original;
		}

		return false;
	}

	/**
	 * Returns an activity stream.
	 *
	 * @static
	 * @param  int    $limit  number of stream items to find
	 * @param  string  $object  object to load
	 * @return array
	 */
	public static function stream($limit = 0, $object = null) {
		$activity_items = array();

		// Load the activities
		$activities = ORM::factory('activity')->items($limit, $object);

		$guids = array();
		foreach ($activities as $activity) {
			$guids[] = $activity->actor;
			$guids[] = $activity->target;
			if ($activity->destination !== null) {
				$guids[] = $activity->destination;
			}
		}
		$guids = array_unique($guids);

		if (count($guids)) {
			// Find the global item values
			$global = array();
			$global_items = ORM::factory('global_item')->where('guid', 'IN', array_unique($guids))->find_all();
			foreach ($global_items as $item) {
				$global[$item->guid] = $item->as_array();

				// Fix the ID
				$guid = explode('_', $item->guid);
				$global[$item->guid]['id'] = array_pop($guid);

				// Cleanup
				unset($guids[$item->guid]);
			}

			// Build the activity stream
			foreach ($activities as $activity) {
				$activity_items[] = array(
					'created' => $activity->created,
					'type' => $activity->type,
					'verb' => $activity->verb,
					'actor' => $global[$activity->actor],
					'target' => $global[$activity->target],
					'destination' => isset($global[$activity->destination]) ? $global[$activity->destination] : null,
				);
			}
		}

		return $activity_items;
	}

	/**
	 * Converts activity items to a list, like OModel's OList.
	 *
	 * @static
	 * @param  array  $items  activity stream items
	 * @return string
	 */
	public static function as_list(array $items = array()) {
		$total_items = count($items);
		if (!$total_items) {
			$content = View::factory('activity/list/row/empty');
		}
		else {
			$content = '';
			$i = 0;
			foreach ($items as $item) {
				++$i;
				$content .= View::factory('activity/list/row/item', array(
					'item' => $item,
					'is_first_item' => ($i === 1),
					'is_last_item' => ($i === $total_items)
				));
			}
		}
		return View::factory('activity/list/shell', array(
			'content' => $content
		))->render();
	}

} // End Oxygen_Activity
