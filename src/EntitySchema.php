<?php
namespace FollowTheMoney;

use FollowTheMoney\Exceptions\FtmException;
use FollowTheMoney\IdGenerator\EntityIdGeneratorInterface;
use FollowTheMoney\Schema\SchemaRegistryInterface;

/**
 * @template-implements \IteratorAggregate<array>
 * @template-implements \ArrayAccess<string,array|null>
 */
class EntitySchema implements \JsonSerializable, \IteratorAggregate, \Countable, \ArrayAccess {
	use EntitySchemaArrayTrait;
	use EntitySchemaFromToTrait;

	/** @var SchemaRegistryInterface */
	protected SchemaRegistryInterface $registry;

	/** @var string */
	protected string $entity;

	/** @var array */
	protected array $schema;

	/** @var EntitySchema[] */
	protected array $parents = [ ];

	/** @var EntityProperty[] */
	protected array $props = [ ];

	/** @var string|null */
	protected ?string $id = null;

	/** @var array */
	protected array $values = [ ];

	/** @var EntityIdGeneratorInterface|null */
	protected ?EntityIdGeneratorInterface $id_generator = null;

	/**
	 * EntitySchema constructor.
	 *
	 * @param string $schema
	 * @param SchemaRegistryInterface $registry
	 */
	public function __construct( string $schema, SchemaRegistryInterface $registry ) {
		$this->entity = $schema;
		$this->registry = $registry;

		$this->schema = $registry->get( $schema );

		$this->loadParentSchemas();
		$this->initProperties();
	}

	/**
	 * Returns list of extended schemas.
	 *
	 * @return array
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
	 *
	 * @param string $property
	 *
	 * @return string
	 */
	public function getPropertyLabel( string $property ) : string {
		return $this->props[ $property ]->label ?? $property;
	}

	/**
	 * @param string $property
	 *
	 * @return string|null
	 */
	public function getPropertyDescription( string $property ) : ?string {
		return $this->props[ $property ]->description ?? null;
	}

	/**
	 * @param string $property
	 *
	 * @return string|null
	 */
	public function getPropertyType( string $property ) : ?string {
		return $this->props[ $property ]->type ?? null;
	}

	/**
	 * Set multiple properties.
	 *
	 * @param array $properties
	 *
	 * @return $this
	 */
	public function setValues( array $properties ) : EntitySchema {
		foreach ( $properties as $prop => $val ) {
			$this->set( $prop, $val );
		}

		return $this;
	}

	/**
	 * Set (overwrite) property value.
	 *
	 * @param string $prop
	 * @param string|string[] $val
	 *
	 * @return $this
	 */
	public function set( string $prop, array|string $val ) : EntitySchema {
		if ( !is_array( $val ) ) {
			$val = [ $val ];
		}

		$this->values[ $prop ] = $val;

		return $this;
	}

	/**
	 * Append value to property.
	 *
	 * @param string $prop
	 * @param string|string[] $val
	 *
	 * @return $this
	 */
	public function append( string $prop, array|string $val ) : EntitySchema {
		if ( !is_array( $val ) ) {
			$val = [ $val ];
		}

		if ( !isset( $this->values[ $prop ] ) ) {
			$this->values[ $prop ] = $val;
		} else {
			$this->values[ $prop ] = array_merge( $this->values[ $prop ], $val );
		}

		return $this;
	}

	/**
	 * @param string|null $id
	 *
	 * @return $this
	 */
	public function setId( ?string $id ) : EntitySchema {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSchemaName() : string {
		return $this->entity;
	}

	/**
	 * @return string
	 */
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
	 * @return array
	 */
	public function getCaptionPropertiesNames() : array {
		if ( !empty( $this->schema[ 'edge' ][ 'caption' ] ) ) {
			$prop = $this->schema[ 'edge' ][ 'caption' ];

			return [ $prop ];
		}

		if ( !empty( $this->schema[ 'caption' ] ) ) {
			return $this->schema[ 'caption' ];
		}

		return [ ];
	}

	/**
	 * @return array
	 */
	public function getCaptionValues() : array {
		$props = $this->getCaptionPropertiesNames();
		$result = [ ];

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
	 *
	 * @return string|null
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
	 *
	 * @return string
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
	 *
	 * @param string $prop
	 *
	 * @return mixed
	 */
	public function get( string $prop ) : mixed {
		return $this->values[ $prop ] ?? null;
	}

	/**
	 * Returns all property values.
	 *
	 * @param string|null $property
	 *
	 * @return array
	 *
	 * @throws FtmException
	 */
	public function values( ?string $property = null ) : array {
		if ( is_string( $property ) ) {
			return $this->getPropertyValues( $property );
		}

		return array_map( fn ( $val ) => is_array( $val ) ? $val : [ $val ], $this->values );
	}

	/**
	 * @param string $prop
	 *
	 * @return array
	 */
	public function getSchemaProp( string $prop ) : array {
		$value = $this->schema[ $prop ] ?? [ ];

		return is_array( $value ) ? $value : [ $value ];
	}

	public function setIdGenerator( EntityIdGeneratorInterface $id_generator ) : static {
		$this->id_generator = $id_generator;

		return $this;
	}

	/**
	 * @param string $property
	 *
	 * @return array
	 *
	 * @throws FtmException
	 */
	private function getPropertyValues( string $property ) : array {
		if ( !isset( $this->props[ $property ] ) ) {
			throw new FtmException( "No property {$property} is defined for schema" );
		}

		if ( empty( $this->values[ $property ] ) ) {
			return [ ];
		}

		return $this->values[ $property ];
	}

	/**
	 * Load and create definitions for all parent entities, their parents, and so on
	 * These will break if there's a loop in hierarchy.
	 */
	private function loadParentSchemas() {
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
	private function initProperties() {
		$props = $this->getSchemaProp( 'properties' );

		foreach ( $this->parents as $parent ) {
			$this->props += $parent->properties();
		}

		foreach ( $props as $name => $prop ) {
			$this->props[ $name ] = new EntityProperty(
				$name,
				$prop[ 'label' ] ?? null,
				$prop[ 'description' ] ?? null,
				$prop[ 'type' ] ?? null,
			);
		}
	}
}
