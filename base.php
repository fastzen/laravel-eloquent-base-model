<?php namespace EloquentBaseModel;

class Base extends \Eloquent
{
	/**
	 * The rules array stores Validator rules in an array indexed by
	 * the field_name to which the rules should be applied.
	 *
	 * @var array
	 */
	public static $rules = array();

	/**
	 * The messages array stores Validator messages in an array indexed by
	 * the field_name to which the messages should be applied in case of errors.
	 *
	 * @var array
	 */
	public static $messages = array();

	/**
	 * The validation object is stored here once is_valid() is run.
	 * This object is publicly accessible so that it can be used
	 * to redirect with errors.
	 *
	 * @var object
	 */
	public $validation = false;

	public static $soft_delete = true;
	
	/**
	 * Validates model.
	 *
	 * @param  array   $input
	 * @return bool
	 */
	public function is_valid()
	{
		if(empty(static::$rules))
		{
			return true;
		}

		// generate the validator and return its success status
		$this->validation = \Validator::make($this->attributes, static::$rules, static::$messages);

		return $this->validation->passes();
	}

	/**
	 * Custom method for model deletion - basically
	 * a soft-delete mechanism. Sets the deleted_at
	 * field on the model.
	 */
	public function delete() {
		if (static::$soft_delete) {
			$this->deleted_at = new \DateTime;
			$this->save();
		}
		else {
			parent::delete();
		}
	}
	
	/**
	 * Overwrite the save method so as to provide easy
	 * access to before and after save methods.
	 */
	public function save() {

        $new_record = !$this->exists;
        if($new_record)
            $this->before_new();

		// we save this information before saving, because as soon as the
		// object is created on the database, this evaluates to true.
		$exists = $this->exists;
		$original = $this->original;

		$this->before_save($exists, $original);
		$result = parent::save();
		
		if ($result)
			$this->after_save($exists, $original);

        if($new_record)
            $this->after_new();

		return $result;
	}

	// Default methods for overloading
	protected function before_save($exists, $original) {}

	// we pass in the new record value because otherwise it
	// would be impossible to ascertain, after the record has been saved.
	protected function after_save($exists, $original) {}

    protected function before_new() {}

    protected function after_new() {}

}