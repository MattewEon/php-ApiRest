<?php
require_once __DIR__ . "/Rest.php";
require_once __DIR__ . "/PDOUtils.php";

/**
 * Repository class are representing SQL tables
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class Repository {
	/** @var string SQL table name */
	public $tableName;
	/** @var mixed SQL index key name */
	public $idKey;
	/** @var array All columns name (without id) */
	public $properties;

	/** @var string PHP Model's classname */
	public $modelName;


	/**
	 * TableHandler constructor
	 *
	 * @param string $tableName
	 * @param string $modelName
	 *
	 * @throws Exception if modelName don't extend Model
	 */
	public function __construct(string $tableName, string $modelName) {
		/** @var Model $model */
		$model = new $modelName();
		if (!is_subclass_of($model, "Model")) throw new Exception("$modelName have to extend the abstract class Model");

		$this->tableName = $tableName;
		$this->modelName = $modelName;

		$keys = [];
		foreach ($model as $key => $value) $keys[] = $key;

		$this->idKey = $keys[0];
		unset($keys[0]);
		foreach (array_keys($keys) as $i) {
			if ($model->isDbIgnore($keys[ $i ])) {
				unset($keys[ $i ]);
			}
		}
		$this->properties = $keys;
	}


	/** Get a new ID
	 *
	 * @return int
	 */
	public function getNewID(): int {
		$last = -1;
		foreach (Rest::$db->query("SELECT $this->idKey FROM $this->tableName ORDER BY $this->idKey ASC") as $result) {
			if ($last + 1 != $result[0]) return $last + 1;
			$last++;
		}
		return $last + 1;
	}

	/**
	 * @param PDOStatement $PDOStatement
	 *
	 * @return Model[]
	 */
	public function getByPDOStatement(PDOStatement $PDOStatement) {
		return PDOUtils::getModelByQuery($PDOStatement, $this->modelName);
	}


	/**
	 * Get the GetAll query
	 *
	 * @return string
	 */
	public function getGetAllQuery(): string {
		return "SELECT * FROM $this->tableName";
	}

	/**
	 * Get all lines
	 *
	 * @return Model[]
	 */
	public function getAll(): array {
		$PDOStatement = PDOUtils::executeQuery($this->getGetAllQuery());
		return $this->getByPDOStatement($PDOStatement);
	}


	/**
	 * Get the GetById query
	 *
	 * @return string
	 */
	public function getGetByIDQuery(): string {
		return $this->getGetByFieldQuery($this->idKey);
	}

	/**
	 * Get a single line by ID
	 *
	 * @param mixed $id
	 *
	 * @return Model
	 */
	public function getByID($id): Model {
		$PDOStatement = PDOUtils::executeQueryWithParameter($this->getGetByIDQuery(), new KeyValue($this->idKey, $id));

		$model = $PDOStatement->fetchObject($this->modelName);
		$model->preserveBooleans();

		return $model;
	}


	/**
	 * Get the GetByField query
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	public function getGetByFieldQuery(string $field): string {
		return "SELECT * FROM $this->tableName WHERE $field = :$field";
	}

	/**
	 * Get a single line by $field
	 *
	 * @param KeyValue $field
	 *
	 * @return Model[]
	 */
	public function getByField(KeyValue $field): array {
		$PDOStatement = PDOUtils::executeQueryWithParameter($this->getGetByFieldQuery($field->key), $field);
		return $this->getByPDOStatement($PDOStatement);
	}


	/**
	 * Get the GetByFields query
	 *
	 * @param string[] $fields
	 *
	 * @return string
	 */
	public function getGetByFieldsQuery(array $fields): string {
		return "SELECT * FROM $this->tableName WHERE " . self::fieldsToQuery($fields);
	}

	/**
	 * Get a single line by ID
	 *
	 * @param KeyValueList $fields
	 *
	 * @return Model[]
	 */
	public function getByFields(KeyValueList $fields): array {
		$PDOStatement = PDOUtils::executeQueryWithParameters($this->getGetByFieldsQuery($fields->getKeys()), $fields);
		return $this->getByPDOStatement($PDOStatement);
	}


	/**
	 * Get the Create query
	 *
	 * @return string
	 */
	public function getCreateQuery(): string {
		$columns = join(", ", $this->properties);
		$keys    = ":" . join(", :", $this->properties);

		return "INSERT INTO $this->tableName ($this->idKey, $columns) VALUES (:$this->idKey, $keys)";
	}

	/**
	 * Create a model
	 *
	 * @param Model $model
	 *
	 * @return Model
	 * @throws Exception
	 */
	public function create(Model $model): Model {
		$id                    = $this->getNewID();
		$model->{$this->idKey} = $id;

		$stmt = Rest::$db->prepare($this->getCreateQuery());
		$this->bindStatement($model, $stmt);
		$stmt->execute();

		return $this->getByID($id);
	}


	/** Get the Update query
	 *
	 * @return string
	 */
	public function getUpdateQuery(): string {
		$setPropertiesArray = [];
		foreach ($this->properties as $key) {
			$setPropertiesArray[] = "$key = :$key";
		}
		$setProperties = join(", ", $setPropertiesArray);

		return "UPDATE $this->tableName SET $setProperties WHERE $this->idKey = :$this->idKey;";
	}

	/** Update model
	 *
	 * @param Model $model
	 *
	 * @return Model
	 * @throws Exception
	 */
	public function update(Model $model): Model {
		$stmt = Rest::$db->prepare($this->getUpdateQuery());
		$this->bindStatement($model, $stmt);
		$stmt->execute();

		return $this->getByID($model->{$this->idKey});
	}


	/** Delete a model
	 *
	 * @param $id
	 */
	public function delete($id) {
		$field = new KeyValue($this->idKey, $id);
		PDOUtils::executeQueryWithParameter($this->getDeleteByFieldQuery($this->idKey), $field);
	}


	/** Get the Delete query with field
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	public function getDeleteByFieldQuery(string $field): string {
		return "DELETE FROM $this->tableName WHERE $field = :$field";
	}

	/**
	 * Delete models by field
	 *
	 * @param KeyValue $field
	 */
	public function deleteByField(KeyValue $field) {
		PDOUtils::executeQueryWithParameter($this->getDeleteByFieldQuery($field), $field);
	}


	/** Get the Delete query with fields
	 *
	 * @param KeyValueList $fields
	 *
	 * @return string
	 */
	public function getDeleteByFieldsQuery(KeyValueList $fields): string {
		return "DELETE FROM $this->tableName WHERE " . self::fieldsToQuery($fields->getKeys());
	}

	/**
	 * Delete models by fields
	 *
	 * @param KeyValueList $fields
	 */
	public function deleteByFields(KeyValueList $fields) {
		PDOUtils::executeQueryWithParameters($this->getDeleteByFieldsQuery($fields), $fields);
	}

	/**
	 * Transform a array of parameter names into a SQL string
	 *    example : ("propA", "propB") => "propA = :propA AND propB = :propB"
	 *
	 * @param array $fields
	 *
	 * @return string
	 */
	public static function fieldsToQuery(array $fields) {
		$fieldsStrArray = [];
		foreach ($fields as $field) $fieldsStrArray[] = "$field = :$field";
		return join(" AND ", $fieldsStrArray);
	}

	/**
	 * Bind Model's attributes to the statement
	 *
	 * @param Model        $model
	 * @param PDOStatement $stmt
	 *
	 * @throws Exception
	 */
	public function bindStatement(Model $model, PDOStatement $stmt) {
		if (!is_a($model, $this->modelName)) throw new Exception("Received a " . get_class($model) . " object, waiting $this->modelName");

		foreach ($model as $key => $value) {
			if (!$model->isDbIgnore($key))
				$stmt->bindValue(":" . $key, $value, PDOUtils::getPdoParam($value));
		}
	}
}
