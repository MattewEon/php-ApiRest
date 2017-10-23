<?php

/** Structure Class used to represent a Key - Value item
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class KeyValue {
	public $key;
	public $value;

	public function __construct(string $key, string $value) {
		$this->key = $key;
		$this->value = $value;
	}

	public function copy(): KeyValue {
		return new KeyValue($this->key, $this->value);
	}

	public function swap() {
		$temp = $this->key;
		$this->key = $this->value;
		$this->value = $temp;
	}
}