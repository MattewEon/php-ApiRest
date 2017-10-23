<?php

/** Credentials class used to generate a Token
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */

class Credentials {
	/** @var string */
	public $id;
	/** @var Role */
	public $role;

	public function __construct(string $id, int $role) {
		$this->id = $id;
		$this->role = $role;
	}
}