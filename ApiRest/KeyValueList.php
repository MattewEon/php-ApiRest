<?php

/** Structure class representing a list of KeyValue items
 *
 * @author Mathieu Gallo <gallo.mathieu@outlook.fr>
 */
class KeyValueList {
	/** @var KeyValue[] List of values */
	public $values;


	/**
	 * KeyValueList constructor.
	 *
	 * @param KeyValue[] $values
	 */
	function __construct(array $values = []) {
		$this->values = $values;
	}


	/**
	 * Get KeyValue by $index
	 *
	 * @param number $index
	 *
	 * @return KeyValue
	 */
	public function get(number $index): KeyValue {
		return $this->values[ $index ];
	}


	/**
	 * Get all keys
	 *
	 * @return string[]
	 */
	public function getKeys(): array {
		$result = [];
		foreach ($this->values as $value)
			$result[] = $value->key;

		return $result;
	}


	/**
	 * Add a KeyValue to the list
	 *
	 * @param KeyValue $element
	 *
	 * @return KeyValueList Equals $this
	 */
	public function add(KeyValue $element): KeyValueList {
		$index = $this->getKeyIndex($element->key);

		if ($index == -1)
			$this->values[] = $element;
		else
			$this->values[ $index ] = $element;

		return $this;
	}


	/**
	 * Get the index of the $key
	 *
	 * @param string $key
	 *
	 * @return int
	 */
	public function getKeyIndex(string $key): int {
		foreach ($this->values as $index => $value)
			if ($value->key == $key)
				return $index;

		return -1;
	}

	/**
	 * Check if a key exists
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function keyExist(string $key): bool {
		foreach ($this->values as $value)
			if ($value->key == $key)
				return true;

		return false;
	}


	/**
	 * Get the size of the list
	 *
	 * @return int
	 */
	public function size(): int {
		return count($this->values);
	}
}