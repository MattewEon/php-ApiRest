<?php
require_once __DIR__ . "/ApiRoute.php";
require_once __DIR__ . "/../Guards/include.php";

/** Controller class calls functions on specific urls
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
abstract class Controller {
	/** @var  Service */
	public static $service;
	/** @var  string */
	public static $modelName;
	/** @var  ApiRoute[] Contains all urls to match */
	protected static $apiRoutes;


	/**
	 * Controller constructor and bind defaults api routes.
	 *
	 * @param string $modelName
	 */
	public function __construct(string $modelName) {
		$serviceName       = $modelName . "Service";
		static::$service   = $serviceName::getInstance($modelName);
		static::$modelName = $modelName;
		static::$apiRoutes = [];

		$this->createApiRoute(Rest::GET, '', "getAll", [new LoginGuard()]);
		$this->createApiRoute(Rest::GET, '$id', "getById", [new LoginGuard()]);

		$this->createApiRoute(Rest::POST, '', "create", [new LoginGuard()]);

		$this->createApiRoute(Rest::PUT, '', "update", [new LoginGuard()]);

		$this->createApiRoute(Rest::DELETE, '$id', 'delete', [new LoginGuard()]);
	}


	/**
	 * Process the url, check if there is a match and call the associate function
	 *
	 * @param string $method Rest::GET POST PUT DELETE
	 * @param string $url
	 * @param string $body
	 *
	 * @return string response
	 * @throws Exception when no match
	 */
	public function processUrl(string $method, string $url, string $body): string {
		$apiUrl    = new ApiUrl($method, $url);
		$data_body = $body == "" ? new stdClass() : json_decode($body);

		/** @var ApiRoute $matchedRoute */
		$matchedRoute = null;
		foreach (static::$apiRoutes as $apiRoute) if ($apiRoute->match($apiUrl)) {
			if ($matchedRoute == null) {
				// Found the first route, we take it
				$matchedRoute = $apiRoute;
			} else if ($matchedRoute->route->getWeight() > $apiRoute->route->getWeight()) {
				// We found a route with less parameters, we take it instead
				$matchedRoute = $apiRoute;
			} else if ($matchedRoute->route->getWeight() == $apiRoute->route->getWeight()) {
				throw new Exception("Route is not unique : '" . static::$modelName . "/$url' ");
			}
		}

		$matchedRoute->checkGuards();
		return $matchedRoute->callFunction($apiUrl, $data_body);

		throw new Exception("URL '" . static::$modelName . "/$url' has no match");
	}


	/**
	 * Add a route match in the right order so that the first route is the good one
	 *
	 * @param ApiRoute $newApiRoute
	 */
	public static function addApiRoute(ApiRoute $newApiRoute) {
		foreach (static::$apiRoutes as $index => $apiRoute) {
			if ($newApiRoute->route->getWeight() > $apiRoute->route->getWeight()) {
				array_splice(static::$apiRoutes, $index, 0, [$newApiRoute]);
				return;
			}
		}

		static::$apiRoutes[] = $newApiRoute;
	}


	/**
	 * Create and add an ApiRoute
	 *
	 * @param string  $method
	 * @param string  $url
	 * @param string  $func
	 * @param Guard[] $guards
	 */
	public static function createApiRoute(string $method, string $url, string $func, array $guards = []) {
		$newApiRoute = new ApiRoute($method, $url, get_called_class() . "::" . $func, $guards);

		foreach (static::$apiRoutes as &$apiRoute) {
			if ($apiRoute->perfectMatch($newApiRoute->route)) {
				$apiRoute = $newApiRoute;
				return;
			}
		}

		self::addApiRoute($newApiRoute);
	}


	/**
	 * Clear the $apiRoutes array
	 */
	public static function clearApiRoutes() {
		static::$apiRoutes = [];
	}


	/**
	 * Create a KeyValueList from $_GET parameters, and filter keys depending of the object
	 *
	 * @return KeyValueList
	 */
	private static function filterGetParamsWithModel(): KeyValueList {
		/** @var Model $model */
		$model = new static::$modelName();
		/** @var string[] $modelParameters */
		$modelParameters = get_object_vars($model);
		/** @var KeyValueList $parameters */
		$parameters = new KeyValueList();

		foreach ($_GET as $key => $value) {
			if ($model->isDbIgnore($key)) continue;
			if (!array_key_exists($key, $modelParameters)) continue;

			$parameters->add(new KeyValue($key, $value));
		}

		return $parameters;
	}


	/**
	 * Get All lines
	 *
	 * @return string JSON
	 */
	public static function getAll(): string {
		$parameters = static::filterGetParamsWithModel();

		if ($parameters->size() == 0)
			return static::getAllNoParameters();
		else
			return static::getAllWithParameters($parameters);
	}


	/**
	 * Get All lines
	 *
	 * @return string JSON
	 */
	private static function getAllNoParameters(): string {
		$all   = static::$service->getAll();
		$array = [];
		foreach ($all as $one) $array[] = $one->filter();
		return json_encode($array);
	}


	/**
	 * Get All lines, filtered by parameters
	 *
	 * @param KeyValueList $parameters
	 *
	 * @return string JSON
	 */
	private static function getAllWithParameters(KeyValueList $parameters): string {
		$all   = static::$service->getByFields($parameters);
		$array = [];
		foreach ($all as $one) $array[] = $one->filter();
		return json_encode($array);
	}


	/**
	 * Get a single line by ID
	 *
	 * @param string[] $params
	 *
	 * @return string JSON
	 */
	public static function getById(array $params): string { return static::$service->getById($params["id"])->toJSON(); }


	/**
	 * Create a model
	 *
	 * @param string[] $params
	 * @param stdClass $body
	 *
	 * @return string JSON
	 */
	public static function create(array $params, stdClass $body): string {
		$model = static::$modelName::fromJSON($body);
		return static::$service->create($model)->toJSON();
	}


	/**
	 * Update a model
	 *
	 * @param string[] $params
	 * @param stdClass $body
	 *
	 * @return string JSON
	 */
	public static function update(array $params, stdClass $body): string {
		$model = static::$modelName::fromJSON($body);
		return static::$service->update($model)->toJSON();
	}


	/**
	 * Delete a model
	 *
	 * @param string[] $params
	 *
	 * @return string ""
	 */
	public static function delete(array $params): string {
		return static::$service->delete($params["id"]);
	}
}