<?php
require_once __DIR__ . "/../ApiRest/Controller.php";

class UserController extends Controller {

	public function __construct(string $modelName) {
		parent::__construct($modelName);

		$this->createApiRoute("PUT", 'login', "login");
	}

	/* PUT */
	public static function login(array $params, stdClass $body): string {
		$user = User::fromJSON($body);
		return static::$service->login($user);
	}
}