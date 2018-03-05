<?php

/** All class who are extending Model must declare the $id key first
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
abstract class Model {
	/** Contains all fields to ignore in JSON */
	private $jsonIgnore = ["jsonIgnore", "booleans", "dbIgnore"];
	/** Contains all fields to cast to boolean (SQL value = '1') */
	private $booleans = [];
	/** Contains all fields to ignore when binding in DB */
	private $dbIgnore = ["jsonIgnore", "booleans", "dbIgnore"];

	/** Get the model from JSON string
	 *
	 * @param stdClass $data JSON data
	 * @return Model $data converted
	 */
	static function fromJSON(stdClass $data) {
		$className = get_called_class();
		$model = new $className();
		foreach ($data as $key => $value)
			if (property_exists($model, $key))
				$model->{$key} = $value;

		return $model;
	}

	/** Convert model to JSON
	 *
	 * @return string JSON data
	 */
	public function toJSON(): string { return json_encode($this->filter()); }

	/** Remove fields to be ignored by JSON conversion
	 *
	 * @return array containing JSON fields
	 */
	public function filter(): array {
		$data = get_object_vars($this);
		$filter = array_flip($this->jsonIgnore);
		return array_diff_key($data, $filter);
	}

	/** Add field(s) on the jsonIgnore array
	 *
	 * @param string[] $fields to hide on JSON conversion
	 */
	public function addIgnore(string ...$fields) {
		foreach ($fields as $field)
			$this->jsonIgnore[] = $field;
	}

	/** Add field(s) on the booleans array
	 *
	 * @param string[] $fields fields to convert
	 */
	public function addBoolean(string ...$fields) {
		foreach ($fields as $field)
			$this->booleans[] = $field;
	}

	/** Add field(s) on the dbIgnore array
	 *
	 * @param string[] $fields fields to ignore when binding statement
	 */
	public function addDbIgnore(string ...$fields) {
		foreach ($fields as $field)
			$this->dbIgnore[] = $field;
	}

	/** Check if the field $field should be ignored when binding statement
	 *
	 * @param string $field
	 *
	 * @return bool True if $field is in $dbIgnore
	 */
	public function isDbIgnore(string $field): bool {
		return in_array($field, $this->dbIgnore);
	}

	/** Transform booleans fields into booleans (SQL value = '1') */
	public function preserveBooleans() {
		foreach ($this->booleans AS $boolean) {
			$this->{$boolean} = $this->{$boolean} == "1";
		}
	}
}