<?php
require_once __DIR__ . "/../ApiRest/Guard.php";
require_once __DIR__ . "/../ApiRest/Rest.php";
require_once __DIR__ . "/../ApiRest/Role.enum.php";

class ModeratorGuard extends Guard {

	function authorizeAccess(): bool {
		$credentials = Rest::getTokenID();
		return $credentials->role == Role::ADMIN || $credentials->role == Role::MODERATOR;
	}
}