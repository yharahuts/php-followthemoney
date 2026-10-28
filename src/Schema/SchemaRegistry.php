<?php
namespace FollowTheMoney\Schema;

/**
 * @psalm-type PropertyDefinition = array{
 *     label?: string,
 *     description?: string,
 *     type?: string,
 * }
 * @psalm-type SchemaDefinition = array{
 *     label?: string,
 *     extends?: string|list<string>,
 *     featured?: list<string>,
 *     caption?: list<string>,
 *     edge?: array{caption?: string|list<string>},
 *     properties?: array<string, PropertyDefinition>,
 * }
 */
interface SchemaRegistry {
	/**
	 * Return true if schema exists.
	 */
	public function has( string $schema ) : bool;

	/**
	 * Return schema definition by it's name.
	 *
	 * @return SchemaDefinition
	 */
	public function get( string $schema ) : array;
}
