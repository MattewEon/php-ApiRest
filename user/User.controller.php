<?php
require_once __DIR__ . "/../ApiRest/Rest.php";
require_once __DIR__ . "/../ApiRest/Controller.php";

class UserController extends Controller {

	public function __construct(string $modelName) {
		parent::__construct($modelName);

		$this->createApiRoute(Rest::PUT, 'login', "login");

		$this->createApiRoute(Rest::POST, 'picture', "uploadPicture");
	}

	/* PUT */
	public static function login(array $params, stdClass $body): string {
		$user = User::fromJSON($body);
		return static::$service->login($user);
	}

	public static function uploadPicture(): string {
		$fileName = "htmlInputName";
		$newName = "profilePicture.png";
		Rest::uploadFile($fileName, $newName);
		return "";
	}
}