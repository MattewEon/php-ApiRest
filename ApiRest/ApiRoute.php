<?php
require_once __DIR__ . "/ApiUrl.php";

/** Contains an ApiUrl and the associated function
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class ApiRoute {
	/** @var ApiUrl */
	private $route;
	/** @var callable */
	private $func;

	/** ApiRoute constructor
	 *
	 * @param string   $method
	 * @param string   $url
	 * @param callable $func
	 */
	public function __construct(string $method, string $url, callable $func) {
		$this->route = new ApiUrl($method, $url);
		$this->func = $func;
	}

	/** Check if it match with an ApiUrl
	 *
	 * @param ApiUrl $requestUrl
	 * @return bool
	 */
	public function match(ApiUrl $requestUrl): bool {
		return $this->route->match($requestUrl);
	}

	/** Call the associate function with the parameters
	 *
	 * @param ApiUrl $requestUrl
	 * @param stdClass $body
	 * @return mixed
	 */
	public function callFunction(ApiUrl $requestUrl, stdClass $body) {
		$func = $this->func;
		return $func($this->getParams($requestUrl), $body);
	}

	/** Get params associated to the current url
	 *
	 * @param ApiUrl $requestUrl
	 * @return array
	 */
	public function getParams(ApiUrl $requestUrl): array { return $this->route->getParams($requestUrl); }
}