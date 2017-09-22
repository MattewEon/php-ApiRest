<?php
require_once __DIR__ . "/../ApiRest/Repository.php";

class UserRepository extends Repository {

	public function __construct() {
		parent::__construct("user", 'User');
	}

	public function login(User $user): string {
		$matchedUsers = $this->getByFields(["name", "password"], $user);

		if (count($matchedUsers) != 1)
			throw new Exception("UserRepository->login() : \$matchedUsers have a size of " . count($matchedUsers) . " instead of 1 !");

		$loggedUser = $matchedUsers[0];
		if ($loggedUser)
			return Rest::IDToToken($loggedUser->id);
		else
			return "";
	}
}