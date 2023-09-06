<?php
namespace FollowTheMoney\IdGenerator;

use FollowTheMoney\EntitySchema;

class EntityIdGenerator implements EntityIdGeneratorInterface {
	/** @var array */
	protected array $properties = [ ];

	/** @var string|null */
	protected string $prefix = 'ntt_id';

	/**
	 * EntityIdGenerator constructor.
	 *
	 * @param array $properties Entity schema properties to generate id on
	 * @param string|null $prefix
	 */
	public function __construct( array $properties, ?string $prefix = null ) {
		$this->properties = $properties;

		if ( !is_null( $prefix ) ) {
			$this->prefix = $prefix;
		}
	}

	/** @inheritdoc  */
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
