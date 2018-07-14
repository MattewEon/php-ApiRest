<?php

/** Contains Error data to be printed in the Frontend
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class ApiException extends Exception {
	/** @var string */
	public $key;
	/** @var array[] */
	public $parameters = [];


	/**
	 * ApiError constructor.
	 *
	 * @param string $key
	 * @param array  $parameters
	 */
	public function __construct(string $key, array $parameters = []) {
		$this->key        = $key;
		$this->parameters = $parameters;
	}


	/**
	 * Add a parameter to the error
	 *
	 * @param string $name  The name of the parameter
	 * @param string $value The value of the parameter
	 */
	public function addParameter(string $name, string $value) {
		$this->parameters[ $name ] = $value;
	}
}