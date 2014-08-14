<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package oxygen
 * @subpackage controllers
 *
 * @property Request $request
 * @property Response $response
 */
abstract class Controller_Oxygen_Core extends Controller_Template {

	/**
	 * The default template view, this can be changed in any controller that extends
	 * this controller. Templates are stored in /application/views/templates/
	 *
	 * @var  View  default template view
	 */
	public $template = 'template';

	/**
	 * @var  View  header override
	 */
	public $header = null;

	/**
	 * @var  View  sidebar override
	 */
	public $sidebar = null;

	/**
	 * @var  View  footer override
	 */
	public $footer = null;

	/**
	 * @var  array  default template parts
	 */
	public $views = array(
		'ajax' => 'ajax',
		'shell' => 'chrome/shell',
		'header' => 'chrome/header',
		'footer' => 'chrome/footer',
		'sidebar' => 'chrome/sidebar',
		'messages' => 'chrome/messages'
	);

	/**
	 * @var  Session
	 */
	public $session;

	/**
	 * @var  Breadcrumbs
	 */
	public $breadcrumbs;

	/**
	 * @var  bool  Favorites enabled?
	 */
	public $favorites_enabled = true;

	/**
	 * @var  Favorites
	 */
	public $favorites = '';

	/**
	 * This method is called before any controller actions are called, this allows
	 * us to define objects to be accessible by all controller actions.
	 */
	public function before() {
		// Set the template
		if ($this->request->is_ajax()) {
			$this->template = $this->views['ajax'];
		}
		else {
			$this->template = $this->views['shell'];
		}

		parent::before();

		// Initialize the session
		$this->session = Session::instance();

		// Initialize the breadcrumbs
		$this->breadcrumbs = Breadcrumbs::factory();

		// Initialize the favorite
		$this->favorites = Favorites::factory(array('show_form' => $this->favorites_enabled));

		// Only define template variables if auto_render is true
		if ($this->auto_render) {
			$this->template->set(array(
				'title' => Oxygen::config('oxygen')->get('name'),
				'header' => '',
				'nav_current' => '',
				'content' => '',
				'sidebar' => '',
				'footer' => '',
				'login' => false,
			));
		}

		// Pagination for search?
		if ($this->request->controller() == 'search' || $this->request->action() == 'search') {
			$pagination = Oxygen::config('pagination')->get('default');
			foreach ($pagination as $key => $value) {
				$value = Oxygen::config('oxygen')->get('search_'.$key);
				if ($value !== null) {
					Oxygen::config('oxygen')->set($key, $value);
				}
			}
		}
	}

	/**
	 * This method is called after every controller action, this is your last chance to make
	 * modifications to data before the view is rendered.
	 */
	public function after() {
		if ($this->auto_render) {

			if ($this->header === null) {
				$header = '';
				if (!empty($this->views['header'])) {
					$header = View::factory($this->views['header']);
				}

				$this->header = $header;
			}

			if ($this->sidebar === null) {
				$sidebar = '';
				if (!empty($this->views['sidebar'])) {
					$sidebar = View::factory($this->views['sidebar'], array(
						'user' => Auth::instance()->get_user()
					));
				}

				$this->sidebar = $sidebar;
			}

			if ($this->footer === null) {
				$footer = '';
				if (!empty($this->views['footer'])) {
					$footer = View::factory($this->views['footer']);
				}

				$this->footer = $footer;
			}

			if (!isset($this->template->extra_scripts)) {
				$this->template->extra_scripts = array();
			}

			if (!isset($this->template->extra_styles)) {
				$this->template->extra_styles = array();
			}

			// Filter the content
			$chrome_shell_content = OHooks::instance()->filter('chrome_shell_content', array(
				'template' => $this->template,
				'breadcrumbs' => $this->breadcrumbs,
				'favorites' => $this->favorites,
				'header' => $this->header,
				'messages' => View::factory('chrome/messages'),
				'sidebar' => $this->sidebar,
				'footer' => $this->footer,
				'page_id' => $this->request->controller().'-'.$this->request->action(),
			));

			// Set the new template object
			$this->template = $chrome_shell_content['template'];

			// Breadcrumbs have a title?
			$breadcrumbs = $chrome_shell_content['breadcrumbs'];
			if ($breadcrumbs->title() === null) {
				$breadcrumbs->title($this->template->title);
			}

			if (isset($this->template->content) && $this->template->content instanceof View) {
				$this->template->content->set(array(
					'breadcrumbs' => $breadcrumbs,
				));

				if (isset($chrome_shell_content['favorites'])) {
					$this->template->content->set(array(
						'favorites' => $chrome_shell_content['favorites'],
					));
				}
			}

			// Set final template content
			$this->template->set(array(
				'title' => HTML::title($chrome_shell_content['template']->title),
				'header' => $chrome_shell_content['header'],
				'messages' => $chrome_shell_content['messages'],
				'sidebar' => $chrome_shell_content['sidebar'],
				'footer' => $chrome_shell_content['footer'],
				'favorites' => $chrome_shell_content['favorites'],
				'page_id' => $chrome_shell_content['page_id'],
				'show_fav_form' => $this->favorites_enabled,
			));
		}

		parent::after();

		if (Oxygen::$profiling && !$this->request->is_ajax()) {
			$this->response->body($this->response->body() . View::factory('profiler/stats')->render());
		}
	}

	/**
	 * Return Generic Error with default message if not provided
	 *
	 * @param string $msg
	 * @param array $keys
	 * @access public
	 * @return void
	 */
	function error($msg = null, $keys = null) {
		if (is_null($msg)) {
			$msg = __('Sorry, an error occurred.');
		}
		else if (!is_null($keys)) {
			$msg = __($msg, $keys);
		}

		throw new Kohana_Exception($msg);
	}

	/**
	 * Return 404 Error with default message if not provided
	 *
	 * @param string $msg
	 * @param array $keys
	 * @access public
	 * @return void
	 */
	function error_404($msg = null, $keys = null) {
		if (is_null($msg)) {
			$msg = __('The requested URL :uri was not found on this server.', array(
				':uri' => HTML::chars($this->request->uri()),
			));
		}
		else if (!is_null($keys)) {
			$msg = __($msg, $keys);
		}

		throw new HTTP_Exception_404($msg);
	}


	/**
	 * Return 403 Error with default message if not provided
	 *
	 * @param string $msg
	 * @param array $keys
	 * @access public
	 * @return void
	 */
	function error_403($msg = null, $keys = null) {
		if (is_null($msg)) {
			$msg = __('You are not authorized to access :uri.', array(
				':uri' => HTML::chars($this->request->uri()),
			));
		}
		else if (!is_null($keys)) {
			$msg = __($msg, $keys);
		}

		throw new Oxygen_Access_Exception($msg);
	}

} // End Controller_Oxygen_Core
