<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) 2010-2011 Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_Form_Field_Date extends Oxygen_Form_Field_Text {

	/**
	 * @var  bool  convert the time?
	 */
	protected $_timeshift = true;

	/**
	 * @var  string  date format
	 */
	protected $_date_format = null;

	/**
	 * @var  string  time format
	 */
	protected $_time_format = null;

	/**
	 * @var  string  data type
	 */
	protected $_data_type = 'date';

	/**
	 * @var  string  default|range
	 */
	protected $_display_type = 'default';

	/**
	 * @var  bool  show time
	 */
	protected $_show_time = false;

	/**
	 * Sets the field view type and then calls [View::__construct].
	 *
	 * @param  string  $type  type of field
	 * @param  array   $data  array of values
	 */
	public function __construct($type = 'text', array $data = null) {
		$this->date_format(Oxygen::config('oxygen')->get('date_format'))
			->time_format(Oxygen::config('oxygen')->get('time_format'));

		parent::__construct(null, $data);
	}

	/**
	 * Sets the timeshift status.
	 *
	 * @param  bool  $timeshit  true|false
	 * @return bool|OField
	 */
	public function timeshift($timeshift = null) {
		if ($timeshift === null) {
			return $this->_timeshift;
		}

		$this->_timeshift = $timeshift;
		return $this;
	}

	/**
	 * Sets the date format of the date object.
	 *
	 * @param  string  $format
	 * @return string|OField
	 */
	public function date_format($format = null) {
		if ($format === null) {
			return $this->_date_format;
		}

		$this->_date_format = $format;
		return $this;
	}

	/**
	 * Sets the time format of the date object.
	 *
	 * @param  string  $format
	 * @return string|OField
	 */
	public function time_format($format = null) {
		if ($format === null) {
			return $this->_time_format;
		}

		$this->show_time(true);
		$this->_time_format = $format;
		return $this;
	}

	/**
	 * Sets the show_time attribute.
	 *
	 * @param  bool  $show_time
	 * @return bool|OField
	 */
	public function show_time($show_time = null) {
		if ($show_time === null) {
			return $this->_show_time;
		}

		$this->_show_time = $show_time;
		return $this;
	}

	/**
	 * Checks to see if the the value should be timeshifted.
	 *
	 * @param  string  $value
	 * @return OField
	 */
	public function value($value = null) {
		if ($value === null) {
			if ($this->_value === null && $this->_default_value !== null) {
				$this->_value = $this->_default_value;
			}
			if ($this->_timeshift === true) {
				if (is_int($this->_value)) {
					return Date::local($this->_value, $this->format());
				}
				else if (is_array($this->_value)) {
					foreach ($this->_value as $i => $timestamp) {
						if (!empty($timestamp)) {
							$this->_value[$i] = Date::local($timestamp, $this->format());
						}
					}
					return $this->_value;
				}
				else {
					return Date::local(strtotime($this->_value), $this->format());
				}
			}
			else {
				return $this->_value;
			}
		}
		else if (is_array($value) && $this->_display_type == 'range') {
			if (Arr::get($_POST, $this->name())) {
				foreach ($value as $i => $timestamp) {
					if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/i', $timestamp)) {
						$format = ($i == 0) ? ' 00:00:00' : ' 23:59:59';
						$value[$i] = Date::utc($timestamp.$format);
					}
				}
			}
			$this->_value = $value;
			return $this;
		}
		else if ($this->_display_type == 'default') {
			$this->_value = $value;
			return $this;
		}

		return parent::value($value);
	}

	/**
	 * Adds the date-pick CSS class if the field is editable.
	 *
	 * @param  string  $file        shell view file name
	 * @param  bool	   $find_shell  set to false to skip finding the shell
	 * @return string
	 */
	public function render($file = null, $find_shell = true) {
		if ($this->_display == 'edit') {
			$this->add_css_class('date-pick');

			if ($this->_default_value === null) {
				$this->_default_value = time();
			}
		}
		else if ($this->_display == 'search') {
			$this->add_css_class('date-pick');

			if ($this->view('search') == 'form/field/search') {
				$this->view('search', 'form/field/date/search/'.$this->_display_type);
			}
		}
		return parent::render($file, $find_shell);
	}

	/**
	 * Sets the shell attributes.
	 */
	protected function set_shell_attributes() {
		parent::set_shell_attributes();

		$this->remove_css_class('has-text', true);
		$this->add_css_class('has-date-'.$this->_display_type, true);
	}

	/**
	 * Builds the timestamp format.
	 *
	 * @return string
	 */
	public function format() {
		if ($this->display() == 'edit') {
			return 'Y-m-d';
		}

		$format = $this->_date_format;
		if ($this->_show_time) {
			$format .= ' '.$this->_time_format;
		}

		return $format;
	}

	/**
	 * Translates the value to UTC time.
	 *
	 * Examples:
	 *
	 *     'now' => Date::utc(time())
	 *     '+1w' => Date::utc(strtotime('+1 week'))
	 *     '+1w2d3h' => Date::utc(strtotime('+1 week 2 days 3 hours'))
	 *
	 * @param  string  $value
	 * @return array|string
	 */
	public function query_translation($value) {
		$keys = array(
			's' => 'second',
			'm' => 'minute',
			'h' => 'hour',
			'd' => 'day',
			'w' => 'week',
			'm' => 'month',
			'y' => 'year',
		);

		if (!in_array($value, array('now'))) {
			$_value = '';
			$time = '';
			for ($i = 0, $j = strlen($value); $i < $j; ++$i) {
				if (!isset($keys[$value[$i]])) {
					$time .= $value[$i];
				}
				else {
					$_value = $time.' '.$keys[$value[$i]];
					if (!in_array((int) $time, array(1, -1))) {
						$_value .= 's';
					}
					$_value .= ' ';
				}
			}

			if (!empty($_value)) {
				$value = $_value;
			}
		}

		if (($ts = strtotime($value)) !== false) {
			// TODO just format, this is a UTC timestmap already
			return Date::utc($ts);
		}

		return parent::query_translation($value);
	}

} // End Oxygen_Form_Field_Date
