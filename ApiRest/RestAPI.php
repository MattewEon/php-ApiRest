<?php
require_once __DIR__ . "/Rest.php";
require_once __DIR__ . "/Controller.php";
require_once __DIR__ . "/ApiException.php";

/** Main class of the Rest API
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class RestAPI {


	/**
	 * RestAPI constructor
	 *
	 * @param string      $host
	 * @param string      $dbName
	 * @param string      $userName
	 * @param string      $password
	 * @param string|null $secretKey Used to make the JWT token
	 */
	public function __construct(string $host, string $dbName, string $userName, string $password, string $secretKey = null) {
		Rest::MysqlConnect($host, $dbName, $userName, $password);
		Rest::$secretKey = $secretKey == null ? "m*;rO)P7^)3'k[F'S~h0Lx7{zN%`6S" : $secretKey;
	}


	/**
	 * Main function : handle the ApiUrl received
	 *
	 * @return string
	 */
	public function handleRequest(): string {
		$request    = explode('/', $this->getUrl());
		$controller = $this->getController(array_shift($request));
		try {
			return $controller->processUrl($this->getMethod(), join("/", $request), $this->getBody());
		} catch (ApiException $exception) {
			header("HTTP/1.1 409");
			echo json_encode($exception);
			exit();
		}
	}


	/**
	 * Return the url called
	 *
	 * @return string
	 */
	public function getUrl(): string { return trim($_SERVER['PATH_INFO'], '/'); }


	/**
	 * Get the controller associated
	 *
	 * @param string $className
	 *
	 * @return Controller
	 * @throws Exception when controller don't exist
	 */
	public function getController(string $className): Controller {
		$controlerIncludeFile = __DIR__ . "/../$className/include.php";
		if (!is_file($controlerIncludeFile))
			throw new Exception("Controller include file '$controlerIncludeFile' not found");

		require_once $controlerIncludeFile;
		$class = $className . "Controller";
		return new $class($className);
	}


	/**
	 * Return the HTTP Request Method Rest GET / POST / PUT / DELETE
	 *
	 * @return string
	 */
	public function getMethod(): string { return $_SERVER['REQUEST_METHOD']; }


	/**
	 * Return the HTTP body content
	 *
	 * @return string
	 */
	public function getBody(): string { return file_get_contents('php://input'); }
}