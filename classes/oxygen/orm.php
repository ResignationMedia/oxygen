<?php defined('SYSPATH') || die('No direct access allowed.');
/**
 * @copyright (c) Crowd Favorite. All Rights Reserved.
 * @package   oxygen/core
 */
class Oxygen_ORM extends Kohana_ORM {

	/**
	 * Creates and returns a new model.
	 *
	 * @chainable
	 * @param   string  $model  Model name
	 * @param   mixed   $id     Parameter for find()
	 * @return  ORM
	 */
	public static function factory($model, $id = NULL) {
		// only append Model_ if we need to
		if (strpos($model, 'Model_') !== 0) {
			// Set class name
			$model = 'Model_'.ucfirst($model);
		}

		return new $model($id);
	}

	protected function _load_values(array $values) {
		if (is_array($this->_created_column) && isset($values[$this->_created_column['column']])) {
			$values[$this->_created_column['column']] = strtotime((int) Text::numeric($values[$this->_created_column['column']]));
		}
		if (is_array($this->_updated_column) && isset($values[$this->_updated_column['column']])) {
			$values[$this->_updated_column['column']] = strtotime((int) Text::numeric($values[$this->_updated_column['column']]));
		}

		return parent::_load_values($values);
	}

	/**
	 * Insert a new object to the database
	 * @param  Validation $validation Validation object
	 * @return ORM
	 */
	public function create(Validation $validation = NULL)
	{
		if ($this->_loaded)
			throw new Kohana_Exception('Cannot create :model model because it is already loaded.', array(':model' => $this->_object_name));

		// Require model validation before saving
		if ( ! $this->_valid)
		{
			$this->check($validation);
		}

		$data = array();
		foreach ($this->_changed as $column)
		{
			// Generate list of column => values
			$data[$column] = $this->_object[$column];
		}

		if (is_array($this->_created_column))
		{
			// Fill the created column
			$column = $this->_created_column['column'];
			$format = $this->_created_column['format'];

			$data[$column] = $this->_object[$column] = ($format === TRUE) ? time() : date($format);
		}
// set updated column as well
		if (is_array($this->_updated_column))
		{
			// Fill the updated column
			$column = $this->_updated_column['column'];
			$format = $this->_updated_column['format'];

			$data[$column] = $this->_object[$column] = ($format === TRUE) ? time() : date($format);
		}

		$result = DB::insert($this->_table_name)
			->columns(array_keys($data))
			->values(array_values($data))
			->execute($this->_db);

		if ( ! array_key_exists($this->_primary_key, $data))
		{
			// Load the insert id as the primary key if it was left out
			$this->_object[$this->_primary_key] = $this->_primary_key_value = $result[0];
		}

		// Object is now loaded and saved
		$this->_loaded = $this->_saved = TRUE;

		// All changes have been saved
		$this->_changed = array();
		$this->_original_values = $this->_object;

		return $this;
	}

} // End Model_Oxygen_ORM
