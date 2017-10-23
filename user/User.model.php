<?php
require_once __DIR__ . "/../ApiRest/Model.php";

class User extends Model {
	public $id;
	public $name;
	public $password;
	public $email;
	public $language;

	public function __construct() {
		$this->addIgnore("password");
	}
}