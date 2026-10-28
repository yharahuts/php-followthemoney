<?php
namespace FollowTheMoney\Schema;

interface SchemaRegistry {
	/**
	 * Return true if schema exists.
	 */
	public function has( string $schema ) : bool;

	/**
	 * Return schema definition by it's name.
	 */
	public function get( string $schema ) : array;
}
