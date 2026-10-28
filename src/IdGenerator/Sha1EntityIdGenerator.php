<?php
namespace FollowTheMoney\IdGenerator;

use FollowTheMoney\EntitySchema;

class Sha1EntityIdGenerator implements EntityIdGenerator {
	protected array $properties = [];
	protected string $prefix = 'ntt_id';

	/**
	 * @param string[] $properties Entity schema properties to generate id from
	 */
	public function __construct( array $properties, ?string $prefix = null ) {
		$this->properties = $properties;

		if ( !is_null( $prefix ) ) {
			$this->prefix = $prefix;
		}
	}

	/** {@inheritDoc} */
	public function generate( EntitySchema $entity ) : string {
		$values = $entity->values();
		asort( $values );

		$values = array_filter( $values, fn ( string $key ) => in_array( $key, $this->properties ), ARRAY_FILTER_USE_KEY );
		$values = array_map( fn ( array $list ) => join( '-', $list ), $values );
		$values = join( '.', $values );

		$id = "{$this->prefix}.{$entity->getSchemaName()}.{$values}";

		return sha1( $id );
	}
}
