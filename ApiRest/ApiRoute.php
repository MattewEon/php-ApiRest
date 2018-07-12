<?php
require_once __DIR__ . "/ApiUrl.php";

/** Contains an ApiUrl and the associated function
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class ApiRoute {
	/** @var ApiUrl */
	public $route;
	/** @var callable */
	public $func;
	/** @var Guard[] */
	public $guards;


	/**
	 * ApiRoute constructor
	 *
	 * @param string   $method
	 * @param string   $url
	 * @param callable $func
	 * @param Guard[]  $guards
	 */
	public function __construct(string $method, string $url, callable $func, array $guards = []) {
		$this->route  = new ApiUrl($method, $url);
		$this->func   = $func;
		$this->guards = $guards;
	}


	/**
	 * Check if it match with an ApiUrl
	 *
	 * @param ApiUrl $requestUrl
	 *
	 * @return bool
	 */
	public function match(ApiUrl $requestUrl): bool {
		return $this->route->match($requestUrl);
	}


	/**
	 * Check if it match perfectly (weight included) with an ApiUrl
	 *
	 * @param ApiUrl $requestUrl
	 *
	 * @return bool
	 */
	public function perfectMatch(ApiUrl $requestUrl): bool {
		return $this->route->perfectMatch($requestUrl);
	}


	/**
	 * Check all guards and throw an Exception if access is not authorized
	 *
	 * @throws Exception
	 */
	public function checkGuards() {
		foreach ($this->guards as $guard) {
			if (!$guard->authorizeAccess())
				throw new Exception("Guard " . get_class($guard) . " refused the access");
		}
	}


	/**
	 * Call the associate function with the parameters
	 *
	 * @param ApiUrl $requestUrl
	 * @param mixed  $body
	 *
	 * @return mixed
	 */
	public function callFunction(ApiUrl $requestUrl, $body) {
		$func = $this->func;
		return $func($this->getParams($requestUrl), $body);
	}


	/**
	 * Get params associated to the current url
	 *
	 * @param ApiUrl $requestUrl
	 *
	 * @return array
	 */
	public function getParams(ApiUrl $requestUrl): array { return $this->route->getParams($requestUrl); }
}