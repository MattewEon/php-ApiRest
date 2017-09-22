<?php
require_once __DIR__ . "/../ApiRest/Repository.php";

class UserRepository extends Repository {

	public function __construct() {
		parent::__construct("user", 'User');
	}

	public function login(User $user): string {
		$stmt = Rest::$db->prepare("SELECT * FROM $this->$this->tableName WHERE name = :name AND password = :password");
		$stmt->bindValue(':name', $user->name, self::getPdoParam($user->name));
		$stmt->bindValue(':password', $user->password, self::getPdoParam($user->name));
		$stmt->execute();

		$loggedUser = $stmt->fetchObject('User');

		if ($loggedUser)
			return Rest::IDToToken($loggedUser->id_crea);
		else return "";
	}
}