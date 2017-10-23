<?php
require_once __DIR__ . "/../ApiRest/Guard.php";
require_once __DIR__ . "/../ApiRest/Rest.php";

class LoginGuard extends Guard {

	function authorizeAccess(): bool {
		return Rest::isLogged();
	}
}