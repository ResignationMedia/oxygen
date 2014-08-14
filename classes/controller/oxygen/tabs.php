<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright  (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package    Oxygen
 * @subpackage Controllers
 *
 * @property Request $request
 */
class Controller_Oxygen_Tabs extends Controller_CRUD {

	/**
	 * @var  array  edit tabs
	 */
	public $_tabs = array();

	/**
	 * Builds the tabs for the controller.
	 *
	 * @param  null|string  $key  set this to override the $_tabs key.
	 * @return array
	 */
	public function build_tabs($key = null) {
		if ($key === null) {
			$key = $this->request->action();
		}

		$tabs = array();
		if (isset($this->_tabs[$key])) {
			foreach ($this->_tabs[$key] as $url => $text) {
				$tabs[$url.$this->request->param('id', '')] = $text;
			}
		}
		return $tabs;
	}

} // END Controller_Tabs
