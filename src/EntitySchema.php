<?php
namespace FollowTheMoney;

use FollowTheMoney\Exceptions\FtmException;
use FollowTheMoney\IdGenerator\EntityIdGenerator;
use FollowTheMoney\Schema\Exceptions\FtmSchemaException;
use FollowTheMoney\Schema\SchemaRegistry;

/**
 * @template-implements \IteratorAggregate<string,array>
 * @template-implements \ArrayAccess<string,array|null>
 */
class EntitySchema implements \JsonSerializable, \IteratorAggregate, \Countable, \ArrayAccess {
	use EntitySchemaArrayTrait;
	use EntitySchemaFromToTrait;
	protected SchemaRegistry $registry;
	protected string $entity;
	protected array $schema;

	/** @var EntitySchema[] */
	protected array $parents = [];

	/** @var EntityProperty[] */
	protected array $props = [];
	protected ?string $id = null;
	protected array $values = [];
	protected ?EntityIdGenerator $id_generator = null;

	public function __construct( string $schema, SchemaRegistry $registry ) {
		$this->entity = $schema;
		$this->registry = $registry;

		$this->schema = $registry->get( $schema );

		$this->loadParentSchemas();
		$this->initProperties();
	}

	/**
	 * Returns list of extended schemas.
	 *
	 * @return string[]
	 */
	public function extends() : array {
		return $this->getSchemaProp( 'extends' );
	}

	/**
	 * Returns all entity properties.
	 *
	 * @return EntityProperty[]
	 */
	public function properties() : array {
		return $this->props;
	}

	/**
	 * Return property label.
	 */
	public function getPropertyLabel( string $property ) : string {
		return $this->props[ $property ]->label ?? $property;
	}

	public function getPropertyDescription( string $property ) : ?string {
		return $this->props[ $property ]->description ?? null;
	}

	public function getPropertyType( string $property ) : ?PropertyType {
		return $this->props[ $property ]->type ?? null;
	}

	/**
	 * Set multiple properties.
	 */
	public function setValues( array $properties ) : static {
		foreach ( $properties as $prop => $val ) {
			$this->set( $prop, $val );
		}

		return $this;
	}

	/**
	 * Set (overwrite) property value.
	 *
	 * @param string|string[] $val
	 */
	public function set( string $prop, array|string $val ) : static {
		if ( !is_array( $val ) ) {
			$val = [$val];
		}

		$this->values[ $prop ] = $val;

		return $this;
	}

	/**
	 * Append value to property.
	 *
	 * @param string|string[] $val
	 */
	public function append( string $prop, array|string $val ) : static {
		if ( !is_array( $val ) ) {
			$val = [$val];
		}

		if ( !isset( $this->values[ $prop ] ) ) {
			$this->values[ $prop ] = $val;
		} else {
			$this->values[ $prop ] = array_merge( $this->values[ $prop ], $val );
		}

		return $this;
	}

	public function setId( ?string $id ) : static {
		$this->id = $id;

		return $this;
	}

	public function getSchemaName() : string {
		return $this->entity;
	}

	public function getSchemaLabel() : string {
		return $this->schema[ 'label' ];
	}

	/**
	 * @return string[]
	 */
	public function getFeaturedProperties() : array {
		return $this->schema[ 'featured' ];
	}

	/**
	 * @return string[]
	 */
	public function getCaptionPropertiesNames() : array {
		if ( !empty( $this->schema[ 'edge' ][ 'caption' ] ) ) {
			$prop = $this->schema[ 'edge' ][ 'caption' ];

			return [$prop];
		}

		if ( !empty( $this->schema[ 'caption' ] ) ) {
			return $this->schema[ 'caption' ];
		}

		return [];
	}

	public function getCaptionValues() : array {
		$props = $this->getCaptionPropertiesNames();
		$result = [];

		foreach ( $props as $property ) {
			$values = $this->values( $property );

			if ( !count( $values ) ) {
				continue;
			}

			$result[ $property ] = $values;
		}

		return $result;
	}

	/**
	 * Will return entity caption based on FtM mapping
	 * In case every property is undefined - will return null.
	 */
	public function getEntityCaption() : ?string {
		$values = $this->getCaptionValues();
		$values = array_shift( $values );

		if ( empty( $values ) ) {
			return null;
		}

		foreach ( $values as $value ) {
			// return first non-empty value for multi-valued props
			// e.g. in case of empty strings in props

			if ( !empty( $value ) ) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * Return entity id.
	 */
	public function getId() : string {
		if ( !is_null( $this->id ) ) {
			return $this->id;
		}

		if ( !is_null( $this->id_generator ) ) {
			return $this->id_generator->generate( $this );
		}

		throw new FtmException( 'No entity_id and no generator is set for entity' );
	}

	/**
	 * Return property value by it's name.
	 */
	public function get( string $prop ) : mixed {
		return $this->values[ $prop ] ?? null;
	}

	/**
	 * Returns all property values.
	 *
	 * @throws FtmException
	 */
	public function values( ?string $property = null ) : array {
		if ( is_string( $property ) ) {
			return $this->getPropertyValues( $property );
		}

		return array_map( fn ( $val ) => is_array( $val ) ? $val : [$val], $this->values );
	}

	public function getSchemaProp( string $prop ) : array {
		$value = $this->schema[ $prop ] ?? [];

		return is_array( $value ) ? $value : [$value];
	}

	public function setIdGenerator( EntityIdGenerator $id_generator ) : static {
		$this->id_generator = $id_generator;

		return $this;
	}

	/**
	 * @throws FtmException
	 */
	private function getPropertyValues( string $property ) : array {
		if ( !isset( $this->props[ $property ] ) ) {
			throw new FtmException( "No property {$property} is defined for schema" );
		}

		if ( empty( $this->values[ $property ] ) ) {
			return [];
		}

		return $this->values[ $property ];
	}

	/**
	 * Load and create definitions for all parent entities, their parents, and so on
	 * These will break if there's a loop in hierarchy.
	 */
	private function loadParentSchemas() : void {
		if ( $this->entity !== 'Thing' ) {
			$this->parents[ ] = new static( 'Thing', $this->registry );
		}

		foreach ( $this->extends() as $entity_name ) {
			$this->parents[ $entity_name ] = new static( $entity_name, $this->registry );
		}
	}

	/**
	 * Init all properties definitions.
	 */
	private function initProperties() : void {
		$props = $this->getSchemaProp( 'properties' );

		foreach ( $this->parents as $parent ) {
			$this->props += $parent->properties();
		}

		foreach ( $props as $name => $prop ) {
			$type = $prop[ 'type' ] ?? PropertyType::String->value;

			$this->props[ $name ] = new EntityProperty(
				$name,
				PropertyType::tryFrom( $type ) ?? throw new FtmSchemaException( "Unknown type {$type} of property {$name} in schema {$this->entity}" ),
				$prop[ 'label' ] ?? null,
				$prop[ 'description' ] ?? null,
			);
		}
	}
}
