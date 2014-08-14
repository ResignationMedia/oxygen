<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Favorites {

	/**
	 * @var  bool  current page already a favorite?
	 */
	protected $_favorite = false;

	/**
	 * @var  string  default view
	 */
	protected $_view = 'favorites/basic';

	/**
	 * @var  string  default title
	 */
	protected $_title = 'Title';

	/**
	 * @var  string  default group
	 */
	protected $_group = '*';

	/**
	 * @var  string  default URL
	 */
	protected $_url = null;

	/**
	 * @var  bool  show form?
	 */
	protected $_show_form = true;

	/**
	 * Creates a new Favorites object.
	 *
	 * @static
	 * @param  array  $config  configuration
	 * @return Favorites
	 */
	public static function factory(array $config = array()) {
		return new Favorites($config);
	}

	/**
	 * Creates a new Favorites object.
	 *
	 * @static
	 * @param  array  $config  configuration
	 */
	public function __construct(array $config = array()) {
		foreach ($config as $key => $value) {
			$this->{'_'.$key} = $value;
		}

		if ($this->_url === null) {
			$this->_url = Request::current()->uri();
		}
	}

	/**
	 * Renders the Favorites view.
	 *
	 * @return  string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * Returns the boolean favorite.
	 *
	 * @return bool
	 */
	public function favorite() {
		return $this->_favorite;
	}

	/**
	 * Sets the Favorites view.
	 *
	 * @param  string  $view
	 * @return Favorites|string
	 */
	public function view($view = null) {
		if ($view === null) {
			return $this->_view;
		}

		$this->_view = $view;
		return $this;
	}

	/**
	 * Sets the Favorites title.
	 *
	 * @param  string  $title
	 * @return Favorites|string
	 */
	public function title($title = null) {
		if ($title === null) {
			return $this->_title;
		}

		$this->_title = $title;
		return $this;
	}

	/**
	 * Sets the Favorites group.
	 *
	 * @param  string  $group
	 * @return Favorites|string
	 */
	public function group($group = null) {
		if ($group === null) {
			return $this->_group;
		}

		$this->_group = $group;
		return $this;
	}

	/**
	 * Sets the Favorites URL.
	 *
	 * @param  string  $url
	 * @return Favorites|string
	 */
	public function url($url = null) {
		if ($url === null) {
			return $this->_url;
		}

		$this->_url = $url;
		return $this;
	}

	/**
	 * Renders the Favorites content.
	 *
	 * @param  string  $view  view to render
	 * @return string
	 */
	public function render($view = null) {
		$favorites = Favorites::load_all(Auth::instance()->get_user()->id);
		if (!empty($favorites)) {
			// Already a favorite?
			foreach ($favorites as $group => $bookmarks) {
				foreach ($bookmarks as $bookmark) {
					if ($this->_url == $bookmark->url()) {
						$this->_favorite = true;
						break;
					}
				}
			}
		}

		if ($view === null) {
			$view = $this->_view;
		}

		return View::factory($view, array('favorites' => $this))->render();
	}

	/**
	 * Loads the favorite form.
	 *
	 * @return string|View
	 */
	public function form() {
		if (!$this->_show_form) {
			return '';
		}

		return View::factory('favorites/form');
	}

	/**
	 * Loads all of the favorites for the requested user.
	 *
	 * @static
	 * @param  Model_User|int  $user
	 * @return object
	 */
	public static function load_all($user) {
		if (is_int($user)) {
			$user = OModel::factory('User', $user);
		}

		$favorites = $user->preference('favorites');
		if (!empty($favorites)) {
			foreach ($favorites as $group => $bookmarks) {
				foreach ($bookmarks as $key => $bookmark) {
					$favorites->$group->$key = Bookmark::factory($bookmark->url, $bookmark->title);
				}
			}
		}

		return $favorites;
	}

} // End Oxygen_Favorites
