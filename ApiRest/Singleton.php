<?php

abstract class Singleton {
	private static $instances = [];

	/**
	 * Singleton constructor.
	 */
	protected function __construct() { }

	/**
	 * Singleton getInstance function
	 *
	 * @param $arg mixed used in the called class
	 *
	 * @return mixed
	 */
	final public static function getInstance($arg) {
		$calledClass = get_called_class();

		if (!isset(Singleton::$instances[ $calledClass ])) {
			Singleton::$instances[ $calledClass ] = new $calledClass($arg);
			Singleton::$instances[ $calledClass ]->initialize();
		}

		return Singleton::$instances[ $calledClass ];
	}

	/**
	 * Singleton initialize variable, to not to call Singleton class in the constructor
	 * avoiding circular calls
	 *
	 * @return void
	 */
	abstract function initialize();

	/**
	 * The function clone cannot be used
	 */
	final private function __clone() { }
}