<?php

/** Contains an URL and his method
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class ApiUrl {
	/** @var array url exploded on "/" */
	public $url;
	/** @var string Rest::GET POST PUT DELETE */
	private $method;

	/** ApiUrl constructor.
	 *
	 * @param string $method
	 * @param string $url
	 * @throws Exception when Rest::method is not valid
	 */
	public function __construct(string $method, string $url) {
		if (!Rest::isAMethod($method))
			throw new Exception("Method $method is not available !");

		$this->method = $method;
		$this->url = explode("/", $url);
	}


	/** Get parameters of url
	 *
	 * @param ApiUrl $requestUrl
	 * @return array of parameters
	 * @throws Exception
	 */
	public function getParams(ApiUrl $requestUrl): array {
		if (!$this->match($requestUrl))
			throw new Exception("getValues error : " . join($requestUrl->url, "/") . " didn't match with " . join($this->url, "/"));

		$result = [];
		foreach ($this->url as $index => $value) {
			if (substr($value, 0, 1) == '$')
				$result[substr($value, 1)] = $requestUrl->url[$index];
		}

		return $result;
	}

	/** Check if an ApiUrl match with current ApiUrl
	 *
	 * @param ApiUrl $requestUrl
	 * @return bool
	 */
	public function match(ApiUrl $requestUrl): bool {
		if ($this->method != $requestUrl->method)
			return false;

		foreach ($this->url as $index => $value) {
			if ($value == $requestUrl->url[$index])
				continue;
			if (substr($value, 0, 1) == '$' && isset($requestUrl->url[$index]))
				continue;
			else return false;
		}

		return count($this->url) == count($requestUrl->url);
	}
}