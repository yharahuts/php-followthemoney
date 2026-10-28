<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\PropertyType;

/**
 * @psalm-type StatementArray = array{id: string, entity_id: string, schema: string, prop: string, prop_type: string, val: string}
 */
readonly class EntityStatement implements \JsonSerializable {
	/**
	 * Property name of a statement holding entity id as a value.
	 */
	public const string ID_PROP = 'id';

	public function __construct(
		public string $id,
		public string $entity_id,
		public string $schema,
		public string $prop,
		public PropertyType $prop_type,
		public string $val,
	) {}

	/**
	 * @return StatementArray
	 */
	public function toArray() : array {
		return [
			'id'        => $this->id,
			'entity_id' => $this->entity_id,
			'schema'    => $this->schema,
			'prop'      => $this->prop,
			'prop_type' => $this->prop_type->value,
			'val'       => $this->val,
		];
	}

	public function toJson( int $flags = JSON_UNESCAPED_UNICODE ) : string {
		return json_encode( $this->toArray(), $flags );
	}

	/** {@inheritDoc} */
	public function jsonSerialize() : array {
		return $this->toArray();
	}
}
