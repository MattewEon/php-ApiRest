<?php
require_once __DIR__ . "/ApiRoute.php";

/** Controller class calls functions on specific urls
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
abstract class Controller {
	/** @var  Service */
	public static $service;
	/** @var  string */
	public static $modelName;
	/** @var  array<ApiRoute> Contains all urls to match */
	protected static $apiRoutes;

	/** Controller constructor and bind defaults api routes.
	 *
	 * @param string $modelName
	 */
	public function __construct(string $modelName) {
		$serviceName = $modelName . "Service";
		static::$service = new $serviceName($modelName);
		static::$modelName = $modelName;
		static::$apiRoutes = [];

		//TODO : match with authoriations
		$this->createApiRoute(Rest::GET, '', "getAll");
		$this->createApiRoute(Rest::GET, '$id', "getById");

		$this->createApiRoute(Rest::POST, '', "create");

		$this->createApiRoute(Rest::PUT, '', "update");

		$this->createApiRoute(Rest::DELETE, '$id', 'delete');
	}

	/** Process the url, check if there is a match and call the associate function
	 *
	 * @param string $method Rest::GET POST PUT DELETE
	 * @param string $url
	 * @param string $body
	 * @return string response
	 * @throws Exception when no match
	 */
	public function processUrl(string $method, string $url, string $body): string {
		$apiUrl = new ApiUrl($method, $url);
		$data_body = $body == "" ? new stdClass() : json_decode($body);

		foreach (static::$apiRoutes as $apiRoute)
			if ($apiRoute->match($apiUrl))
				return $apiRoute->callFunction($apiUrl, $data_body);

		throw new Exception("URL '" . static::$modelName . "/$url' has no match");
	}

	/** Add a route match
	 *
	 * @param ApiRoute $apiRoute
	 */
	public static function addApiRoute(ApiRoute $apiRoute) { static::$apiRoutes[] = $apiRoute; }

	/** Create and add an ApiRoute
	 *
	 * @param string $method
	 * @param string $url
	 * @param string $func
	 */
	public static function createApiRoute(string $method, string $url, string $func) {
		self::addApiRoute(new ApiRoute($method, $url, get_called_class() . "::" . $func));
	}

	/** Get All lines
	 *
	 * @return string JSON
	 */
	public static function getAll(): string {
		$all = static::$service->getAll();
		$array = [];
		foreach ($all as $one)
			$array[] = $one->filter();
		return json_encode($array);
	}

	/** Get a single line by ID
	 *
	 * @param array <string> $params
	 * @return string JSON
	 */
	public static function getById(array $params): string { return static::$service->getById($params["id"])->toJSON(); }

	/** Create a model
	 *
	 * @param          array <string> $params
	 * @param stdClass $body
	 * @return string JSON
	 */
	public static function create(array $params, stdClass $body): string {
		$user = static::$modelName::fromJSON($body);
		return static::$service->create($user)->toJSON();
	}

	/** Update a model
	 *
	 * @param          array <string> $params
	 * @param stdClass $body
	 * @return string JSON
	 */
	public static function update(array $params, stdClass $body): string {
		$user = static::$modelName::fromJSON($body);
		return static::$service->update($user)->toJSON();
	}

	/** Update a model
	 *
	 * @param array <string> $params
	 * @return string ""
	 */
	public static function delete(array $params): string {
		return static::$service->delete($params["id"]);
	}
}