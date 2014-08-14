<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Controller_Oxygen_Resources extends Controller {

	/**
	 * Serves minified JS and CSS.
	 */
	public function action_index() {
		// Autoloading for Minify
		require Kohana::find_file('vendor/minify', 'Minify');

		// Initialize Minify
		$this->_minify_init();

		// Load the resource
		$key = $this->request->param('key', '');
		$resource = OResource::instance($key);
		switch ($resource->type()) {
			case 'js':
				$type = Minify::TYPE_JS;
			break;
			case 'css':
			default:
				$type = Minify::TYPE_CSS;
			break;
		}
		$config = $resource->compile();

		if (Oxygen::$environment == Oxygen::PRODUCTION || defined('MINIFY_RESOURCES')) {
			// Combine content and get latest modified date
			$combined = $this->combined_content($config, $type);
			extract($combined); // $content and $modified
			$minify_config = compact('content', 'key', 'modified', 'type');

			// Set expires
			$expires = OHooks::instance()->filter('resource_expires_'.$key, (60 * 60 * 24)); // 1 Day
			$this->_serve($minify_config, $expires);
		}
		else {
			// Don't minify in development environments, or if the MINIFY_RESOURCES constant hasn't been defined.
			$content = '';
			foreach ($config as $item) {
				if (isset($item['path'])) {
					$content .= file_get_contents($item['path']);
				}
				else {
					$content .= $item['content'];
				}
			}

			$this->response->status(200);
			$this->response->headers(array(
				'content-length' => strlen($content),
				'content-type' => $type.'; charset=utf-8'
			));
			$this->response->body($content);
		}
	}

	/**
	 * Initializes Minify
	 */
	private function _minify_init() {
		set_include_path(OXYPATH.'vendor'.DIRECTORY_SEPARATOR.'minify');
		extract(OHooks::instance()->filter('minify_config', array(
			'min_allowDebugFlag' => false,
			'min_errorLogger' => false,
			'min_enableBuilder' => false,
			'min_documentRoot' => '',
			'min_cacheFileLocking' => true,
			'min_serveOptions' => array(
				'bubbleCssImports' => false,
				'maxAge' => 1800,
				'minApp' => array(
					'groupsOnly' => true,
					'maxFiles' => 10
				)
			),
			'min_symLinks' => array(),
			'min_uploaderHoursBehind' => 0
		)));

		// Try to disable the output_compression (may not have an effect)
		@ini_set('zlib.output_compression', 0);
		Minify::setCache();
	}

	/**
	 * Serves the content.
	 *
	 * @param  array    $config   Minify config
	 * @param  int	  $expires  file lifetime in seconds
	 */
	private function _serve(array $config, $expires) {
		$version = Oxygen::config('oxygen')->get('resource_version', 0);
		if (!$version) {
			$version = $this->request->param('version', time());
		}
		$serve = Minify::serve('Files', array(
			'files' => array(
				new Minify_Source(array(
					'id' => Oxygen::config('oxygen')->get('app_name')
						.'_source_'.$config['key']
						.'_v_'.$version,
					'content' => $config['content'],
					'contentType' => $config['type'],
					'lastModified' => $config['modified']
				))
			),
			'quiet' => true,
			'maxAge' => $expires
		));

		// Convert ints to strings
		$headers = array();
		foreach ($serve['headers'] as $key => $value)
		{
			if (is_int($value)) {
				$value = (string) $value;
			}
			$headers[strtolower($key)] = $value;
		}
		$serve['headers'] = $headers;

		$this->response->status($serve['statusCode']);
		$this->response->headers($serve['headers']);
		$this->response->body($serve['content']);
	}

	/**
	 * Combines an array of content into a string.
	 *
	 * Expects the following format:
	 *
	 *	 $content = array(
	 *		 array(
	 *			 'key' => 'my-file', // must be unique
	 *			 'type' => 'file',
	 *			 'path' => '/path/to/file',
	 *			 'dep' => array(
	 *				 'jquery' // other keys
	 *			 )
	 *		 ),
	 *		 array(
	 *			 'key' => 'my-content',
	 *			 'type' => 'source',
	 *			 'content' => '.test { text-align: center; }',
	 *			 'modified' => time(),
	 *			 'dep' => array(
	 *				 'jquery' // other keys
	 *			 )
	 *		 )
	 *	 );
	 *
	 * @param  array   $sources  content sources
	 * @param  string  $type
	 *
	 * @return array
	 */
	private function combined_content(array $sources, $type) {
		$content = '';
		$modified = 0;
		if (count($sources)) {
			foreach ($sources as $key => $source) {
				if (!isset($source['type'])) {
					if (isset($source['content'])) {
						$source['type'] = 'source';
					}
					else if (isset($source['path'])) {
						$source['type'] = 'file';
					}
					else {
						// something ain't right
						continue;
					}
				}
				switch ($source['type']) {
					case 'source':
						$content .= $source['content']."\n\n";
						break;
					case 'file':
						if (is_file($source['path'])) {
							$file = file_get_contents($source['path']);

							// CSS file?
							// TODO [AE] check out this logic to support multiple locations of CSS files.
							/*if ($type == Minify::TYPE_CSS && isset($source['rel_to_abs']) && $source['rel_to_abs'] === true) {

								// Convert relative to absolute paths
								$path = explode('/', str_replace(DOCROOT, '', pathinfo($source['path'], PATHINFO_DIRNAME)));

								$relative = '';
								for ($i = 0, $j = count($path); $i < $j; ++$i) {
									$relative .= '../';
								}

								for ($i = 0, $j = count($path); $i < $j; ++$i) {
									$absolute = URL::base('http', false);
									for ($k = 0; $k < $i; ++$k) {
										$absolute .= $path[$k].'/';
									}

									$file = str_replace($relative, $absolute, $file);
									$relative = substr($relative, 0, -3);
								}
							}*/

							// Add to content
							$content .= $file."\n\n";

							$source['modified'] = filemtime($source['path']);
						}
						break;
				}

				if (isset($source['modified']) && $modified < $source['modified']) {
					$modified = $source['modified'];
				}
			}
		}
		return compact('content', 'modified');
	}

} // End Controller_Oxygen_Resources
