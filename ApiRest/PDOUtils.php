<?php

class PDOUtils {
	/**
	 * Execute a Sql Query
	 *
	 * @param string $query the query to execute
	 *
	 * @return PDOStatement
	 */
	public static function executeQuery(string $query): PDOStatement {
		$PDOStatement = Rest::$db->prepare($query);
		$PDOStatement->execute();
		return $PDOStatement;
	}

	/**
	 * Execute a Sql Query with one parameter
	 *
	 * @param string   $query the query to execute
	 * @param KeyValue $field the parameter to bind
	 *
	 * @return PDOStatement
	 */
	public static function executeQueryWithParameter(string $query, KeyValue $field): PDOStatement {
		$PDOStatement = Rest::$db->prepare($query);
		$PDOStatement->bindValue(":$field->key", $field->value, self::getPdoParam($field->value));
		$PDOStatement->execute();
		return $PDOStatement;
	}

	/**
	 * Execute a Sql Query with parameters
	 *
	 * @param string       $query  the query to execute
	 * @param KeyValueList $fields parameters to bind
	 *
	 * @return PDOStatement
	 */
	public static function executeQueryWithParameters(string $query, KeyValueList $fields): PDOStatement {
		$PDOStatement = Rest::$db->prepare($query);
		foreach ($fields->values as $field) {
			$PDOStatement->bindValue(":$field->key", $field->value, self::getPdoParam($field->value));
		}
		$PDOStatement->execute();
		return $PDOStatement;
	}

	/**
	 * Get by a PDO Statement
	 *
	 * @param PDOStatement $PDOStatement The sql query
	 *
	 * @return array Results of the query
	 */
	public static function getByQuery(PDOStatement $PDOStatement): array {
		$results = [];
		foreach ($PDOStatement->fetchAll() as $row) {
			$results[] = $row;
		}


		return $results;
	}

	/**
	 * Get by a PDO Statement
	 *
	 * @param PDOStatement $PDOStatement The sql query
	 * @param string       $modelName    The name of the PHP Model class to use
	 *
	 * @return Model[] Results of the query
	 */
	public static function getModelByQuery(PDOStatement $PDOStatement, string $modelName): array {
		$modelArray = [];
		foreach ($PDOStatement->fetchAll(PDO::FETCH_CLASS, $modelName) as $row) {
			$modelArray[] = $row;
		}

		foreach ($modelArray as $model) $model->preserveBooleans();

		return $modelArray;
	}

	/**
	 * Get the PDO param constant
	 *
	 * @param mixed $value to evaluate
	 *
	 * @return int PDO::PARAM
	 */
	public static function getPdoParam($value): int {
		if (is_bool($value)) return PDO::PARAM_BOOL;
		if (is_numeric($value)) return PDO::PARAM_INT;
		return PDO::PARAM_STR;
	}
}