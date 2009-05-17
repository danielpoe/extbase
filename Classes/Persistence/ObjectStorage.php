<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * The storage for objects. It ensures the uniqueness of an object in the storage. It's a remake of the
 * SplObjectStorage introduced in PHP 5.3.
 *
 * @package Extbase
 * @subpackage extbase
 * @version $ID:$
 */
class Tx_Extbase_Persistence_ObjectStorage implements Iterator, Countable, ArrayAccess {

	/**
	 * The array holding references of the stored objects
	 *
	 * @var array
	 */
	private $storage = array();

	/**
	 * Resets the array pointer of the storage
	 *
	 * @return void
	 */
	public function rewind() {
		reset($this->storage);
	}

	/**
	 * Checks if the array pointer of the storage points to a valid position
	 *
	 * @return void
	 */
	public function valid() {
		return $this->current() !== FALSE;
	}

	/**
	 * Returns the current key storage array
	 *
	 * @return void
	 */
	public function key() {
		return key($this->storage);
	}

	/**
	 * Returns the current value of the storage array
	 *
	 * @return void
	 */
	public function current() {
		return current($this->storage);
	}

	/**
	 * Returns the next position of the storage array
	 *
	 * @return void
	 */
	public function next() {
		next($this->storage);
	}

	/**
	 * Counts the elements in the storage array
	 *
	 * @return void
	 */
	public function count() {
		return count($this->storage);
	}

	/**
	 * Loads the array at a given offset. Nothing happens if the object already exists in the storage 
	 *
	 * @param string $offset 
	 * @param string $obj The object
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if (!is_object($offset)) throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter 1 to be object, ' . gettype($offset) . ' given');
		// if (!is_object($obj)) throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter 2 to be object, ' . gettype($offset) . ' given');
		// if (!($offset === $obj)) throw new Tx_Extbase_Exception_InvalidArgumentType('Parameter 1 and parameter 2 must be a reference to the same object.');
		if (!$this->contains($offset)) {
			$this->storage[spl_object_hash($offset)] = $value;
		}
	}

	/**
	 * Checks if a given offset exists in the storage
	 *
	 * @param string $offset 
	 * @return boolean TRUE if the given offset exists; otherwise FALSE
	 */
	public function offsetExists($offset) {
		if (!is_object($offset)) throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter to be an object, ' . gettype($offset) . ' given');
		return isset($this->storage[spl_object_hash($offset)]);
	}

	/**
	 * Unsets the storage at the given offset
	 *
	 * @param string $offset The offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		if (!is_object($offset)) throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter to be an object, ' . gettype($offset) . ' given');
		unset($this->storage[spl_object_hash($offset)]);
	}

	/**
	 * Returns the object at the given offset
	 *
	 * @param string $offset The offset
	 * @return Object The object
	 */
	public function offsetGet($offset) {
		if (!is_object($offset)) throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter to be an object, ' . gettype($offset) . ' given');
		return isset($this->storage[spl_object_hash($offset)]) ? $this->storage[spl_object_hash($offset)] : NULL;
	}

	/**
	 * Checks if the storage contains the given object
	 *
	 * @param Object $object The object to be checked for
	 * @return boolean TRUE|FALSE Returns TRUE if the storage contains the object; otherwise FALSE
	 */
	public function contains($object) {
		if (!is_object($object)) throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter to be an object, ' . gettype($object) . ' given');
		return array_key_exists(spl_object_hash($object), $this->storage);
	}

	/**
	 * Attaches an object to the storage
	 *
	 * @param Object $obj The Object to be attached
	 * @return void
	 */
	public function attach($object, $value = NULL) {
		if (!is_object($object)) throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter to be an object, ' . gettype($object) . ' given');
		if (!$this->contains($object)) {
			if ($value === NULL) {
				$value = $object;
			}
			$this->storage[spl_object_hash($object)] = $value;
		}
	}

	/**
	 * Detaches an object from the storage
	 *
	 * @param Object $object The object to be removed from the storage
	 * @return void
	 */
	public function detach($object) {
		if (!is_object($object)) throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter to be an object, ' . gettype($object) . ' given');
		unset($this->storage[spl_object_hash($object)]);
	}

	/**
	 * Attach all objects to the storage
	 *
	 * @param array $objects The objects to be attached to the storage
	 * @return void
	 */
	public function addAll($objects) {
		if (is_array($objects) || ($objects instanceof Tx_Extbase_Persistence_ObjectStorage)) {
			foreach ($objects as $object) {
				$this->attach($object);
			}
		} else {
		 throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter to be an array, ' . gettype($object) . ' given');
		}
	}

	/**
	 * Detaches all objects from the storage
	 *
	 * @param array $objects The objects to be detached from the storage
	 * @return void
	 */
	public function removeAll($objects) {
		if (!is_array($object)) throw new Tx_Extbase_Exception_InvalidArgumentType('Expected parameter to be an array, ' . gettype($object) . ' given');
		foreach ($objects as $object) {
			$this->detach($object);
		}
	}

	/**
	 * Returns this object storage as an array
	 *
	 * @return array The object storage
	 */
	public function toArray() {
		return $this->storage;
	}

}

?>