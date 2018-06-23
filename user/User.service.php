<?php
require_once __DIR__ . "/../ApiRest/Service.php";

class UserService extends Service {
	public function login(User $user): string {
		return $this->repository->login($user);
	}

	function initialize() { }
}