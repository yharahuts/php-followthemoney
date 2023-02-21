<?php
namespace FollowTheMoney\Schema;

interface SchemaRegistryInterface {
	/**
	 * Return true if schema exists.
	 *
	 * @param string $schema
	 *
	 * @return bool
	 */
	public function has( string $schema ) : bool;

	/**
	 * Return schema definition by it's name.
	 *
	 * @param string $schema
	 *
	 * @return array
	 */
	public function get( string $schema ) : array;
}
