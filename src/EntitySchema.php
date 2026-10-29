<?php
namespace FollowTheMoney;

use FollowTheMoney\Exceptions\FtmException;
use FollowTheMoney\IdGenerator\EntityIdGenerator;
use FollowTheMoney\Schema\Exceptions\FtmSchemaException;
use FollowTheMoney\Schema\SchemaRegistry;

/**
 * @psalm-import-type SchemaDefinition from SchemaRegistry
 *
 * @psalm-consistent-constructor
 *
 * @template-implements \IteratorAggregate<string,string[]>
 * @template-implements \ArrayAccess<string,string[]>
 */
class EntitySchema implements \JsonSerializable, \IteratorAggregate, \Countable, \ArrayAccess {
	use EntitySchemaArrayTrait;
	use EntitySchemaFromToTrait;
	protected SchemaRegistry $registry;
	protected string $entity;

	/** @var SchemaDefinition */
	protected array $schema;

	/** @var EntitySchema[] */
	protected array $parents = [];

	/** @var array<string, EntityProperty> */
	protected array $props = [];
	protected ?string $id = null;

	/** @var array<string, string[]> */
	protected array $values = [];
	protected ?EntityIdGenerator $id_generator = null;

	/**
	 * Instances with no values, by registry, class and schema name. Schema definition, parents and properties
	 * depend on nothing else, so they are resolved once and copied from here. The entry is dropped with the registry.
	 *
	 * @var \WeakMap<SchemaRegistry, array<class-string, array<string, EntitySchema>>>|null
	 */
	private static ?\WeakMap $prototypes = null;

	public function __construct( string $schema, SchemaRegistry $registry ) {
		$this->entity = $schema;
		$this->registry = $registry;

		/** @var \WeakMap<SchemaRegistry, array<class-string, array<string, EntitySchema>>> $prototypes */
		$prototypes = self::$prototypes ?? new \WeakMap();
		$cache = $prototypes[ $registry ] ?? [];

		if ( isset( $cache[ static::class ][ $schema ] ) ) {
			$prototype = $cache[ static::class ][ $schema ];

			$this->schema = $prototype->schema;
			$this->parents = $prototype->parents;
			$this->props = $prototype->props;

			return;
		}

		$this->schema = $registry->get( $schema );

		$this->loadParentSchemas();
		$this->initProperties();

		// read the cache again, as loadParentSchemas() has added the parents to it
		$prototypes = self::$prototypes ?? $prototypes;
		$cache = $prototypes[ $registry ] ?? [];
		$cache[ static::class ][ $schema ] = clone $this;
		$prototypes[ $registry ] = $cache;
		self::$prototypes = $prototypes;
	}

	/**
	 * Returns a list of extended schemas.
	 *
	 * @return string[]
	 */
	public function extends() : array {
		$extends = $this->schema[ 'extends' ] ?? [];

		return is_array( $extends ) ? $extends : [$extends];
	}

	/**
	 * Returns all entity properties.
	 *
	 * @return array<string, EntityProperty>
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
	 *
	 * @param array<string, string|string[]> $properties
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
		return $this->schema[ 'label' ] ?? $this->entity;
	}

	/**
	 * @return string[]
	 */
	public function getFeaturedProperties() : array {
		return $this->schema[ 'featured' ] ?? [];
	}

	/**
	 * @return string[]
	 */
	public function getCaptionPropertiesNames() : array {
		$edge_caption = $this->schema[ 'edge' ][ 'caption' ] ?? [];

		if ( $edge_caption !== [] && $edge_caption !== '' ) {
			return is_array( $edge_caption ) ? $edge_caption : [$edge_caption];
		}

		return $this->schema[ 'caption' ] ?? [];
	}

	/**
	 * @return array<string, string[]>
	 */
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
	 * Returns entity caption based on FtM schema: the first non-empty value
	 * of the first caption property that has values, or null if there's none.
	 */
	public function getEntityCaption() : ?string {
		$values = $this->getCaptionValues();
		$values = array_shift( $values ) ?? [];

		foreach ( $values as $value ) {
			// return the first non-empty value of a multi-valued property,
			// e.g. skip empty strings

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
	 * Return property values by its name.
	 *
	 * @return string[]|null
	 */
	public function get( string $prop ) : mixed {
		return $this->values[ $prop ] ?? null;
	}

	/**
	 * Returns all property values.
	 *
	 * @return ($property is null ? array<string, string[]> : string[])
	 *
	 * @throws FtmException
	 */
	public function values( ?string $property = null ) : array {
		if ( is_string( $property ) ) {
			return $this->getPropertyValues( $property );
		}

		return $this->values;
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
	 * @return string[]
	 *
	 * @throws FtmException
	 */
	private function getPropertyValues( string $property ) : array {
		if ( !isset( $this->props[ $property ] ) ) {
			throw new FtmException( "No property {$property} is defined for schema" );
		}

		return $this->values[ $property ] ?? [];
	}

	/**
	 * Load and create definitions for all parent entities, their parents, and so on.
	 * This will break if there's a loop in the hierarchy.
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
	 * Init all property definitions.
	 */
	private function initProperties() : void {
		$props = $this->schema[ 'properties' ] ?? [];

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
