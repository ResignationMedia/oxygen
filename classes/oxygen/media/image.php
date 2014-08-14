<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Media_Image extends Media {

	/**
	 * @var  bool  resize the image?
	 */
	protected $resize = true;

	/**
	 * @var  int  default resize method
	 */
	protected $resize_method = Image::INVERSE;

	/**
	 * @var  int  resize width
	 */
	protected $width = 0;

	/**
	 * @var  int  resize height
	 */
	protected $height = 0;

	/**
	 * @var  bool  crop image?
	 */
	protected $crop = false;

	/**
	 * Turns on/off image resizing.
	 *
	 * @param  bool  $resize
	 * @return bool|Oxygen_Media_Image
	 */
	public function resize($resize = null) {
		if ($resize === null) {
			return $this->resize;
		}

		$this->resize = $resize;
		return $this;
	}

	/**
	 * Alter the image and then save it.
	 *
	 * @return Media
	 */
	protected function _save() {
		// Resize the image
		$image = Image::factory($this->_temp);

		// Resize?
		if ($this->resize) {
			$image->resize($this->width, $this->height, $this->resize_method);
		}

		// Crop methods
		if ($this->crop) {
			$image->crop($this->width, $this->height);
		}

		// Save the image
		$image->save($this->_path);

		return $this->_path;
	}

} // End Oxygen_Media_Image
