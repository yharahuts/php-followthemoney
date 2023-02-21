<?php
namespace FollowTheMoney;

use FollowTheMoney\Exceptions\FtmException;

/**
 * @template-implements \IteratorAggregate<array>
 * @template-implements \ArrayAccess<string,array|null>
 */
class EntitySchema implements \JsonSerializable, \IteratorAggregate, \Countable, \ArrayAccess {
	use EntitySchemaArrayTrait;
	use EntitySchemaFromToTrait;

	/** @var string */
	protected string $dir;

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

	/**
	 * EntitySchema constructor.
	 *
	 * @param string $schema
	 * @param string $dir
	 */
	public function __construct( string $schema, string $dir ) {
		$this->entity = $schema;
		$this->dir = $dir;

		// todo: create schema provider factory class, and don't read yamls here
		$filename = "{$this->dir}/{$this->entity}.yaml";

		if ( !file_exists( $filename ) ) {
			throw new FtmException( "No definitions file for schema {$schema} exists in {$dir}" );
		}

		$yaml = yaml_parse_file( $filename );
		$this->schema = $yaml[ $schema ];

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
	 * @param array|string $val
	 *
	 * @return $this
	 */
	public function set( string $prop, $val ) : EntitySchema {
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
	 * @param array|string $val
	 *
	 * @return $this
	 */
	public function append( string $prop, $val ) : EntitySchema {
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
	 * Will return entity caption based on FtM mapping
	 * In case every property is undefined - will return null.
	 *
	 * @return string|null
	 */
	public function getEntityCaption() : ?string {
		$caption_properties = $this->schema[ 'caption' ] ?? [ ];

		foreach ( $caption_properties as $property ) {
			$values = $this->values()[ $property ] ?? [ ];

			// return first non-empty value
			foreach ( $values as $val ) {
				if ( $val ) {
					return $val;
				}
			}
		}

		return null;
	}

	/**
	 * Return entity id.
	 *
	 * @return string|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Return property value by it's name.
	 *
	 * @param string $prop
	 *
	 * @return mixed|null
	 */
	public function get( string $prop ) {
		return $this->values[ $prop ] ?? null;
	}

	/**
	 * Returns all property values.
	 *
	 * @return array
	 *
	 * @todo: return values for specified property
	 */
	public function values() {
		return array_map( function ( $val ) {
			return is_array( $val ) ? $val : [ $val ];
		}, $this->values );
	}

	/**
	 * @param string $prop
	 *
	 * @return array
	 */
	protected function getSchemaProp( string $prop ) {
		$value = $this->schema[ $prop ] ?? [ ];

		return is_array( $value ) ? $value : [ $value ];
	}

	/**
	 * Load and create definitions for all parent entities, their parents, and so on
	 * These will break if there's a loop in hierarchy.
	 */
	private function loadParentSchemas() {
		if ( $this->entity !== 'Thing' ) {
			$this->parents[ ] = new static( 'Thing', $this->dir );
		}

		foreach ( $this->extends() as $entity_name ) {
			$this->parents[ $entity_name ] = new static( $entity_name, $this->dir );
		}
	}

	/**
	 * Init all properties definitions.
	 */
	private function initProperties() {
		$props = $this->getSchemaProp( 'properties' );

		foreach ( $this->parents as $parent ) {
			$this->props = $this->props + $parent->properties();
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
