<?php

/** Superclass of all Guards
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
abstract class Guard {
	abstract function authorizeAccess(): bool;
}