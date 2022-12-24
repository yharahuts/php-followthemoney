<?php
	namespace FollowTheMoney;

	class EntitySchema implements \JsonSerializable, \IteratorAggregate, \Countable, \ArrayAccess {
		use EntitySchemaArrayTrait, EntitySchemaFromToTrait;

		/** @var string */
		protected string $dir;
		/** @var string  */
		protected string $entity;
		/** @var object */
		protected $schema;

		/** @var EntitySchema[] */
		protected array $parents = [ ];
		/** @var EntityProperty[ ] */
		protected $props = [ ];

		/** @var string|null */
		protected ?string $id = null;
		/** @var array */
		protected array $values = [ ];

		/**
		 * EntitySchema constructor.
		 * @param string $entity
		 * @param string $dir
		 */
		public function __construct( string $entity, string $dir ) {
			$this->entity = $entity;
			$this->dir = $dir;

			// todo: create schema reader factory class, and don't read yamls here
			$yaml = yaml_parse_file( "{$this->dir}/{$this->entity}.yaml" );
			$this->schema = $yaml[ $entity ];

			$this->loadParentSchemas( );
			$this->initProperties( );
		}

		/**
		 * Load and create definitions for all parent entities, their parents, and so on
		 * These will break if there's a loop in hierarchy
		 */
		private function loadParentSchemas( ) {
			if( $this->entity !== 'Thing' ) {
				$this->parents[ ] = new static( 'Thing', $this->dir );
			}

			foreach( $this->extends( ) as $entity_name ) {
				$this->parents[ $entity_name ] = new static( $entity_name, $this->dir );
			}
		}

		/**
		 * Init all properties definitions
		 */
		private function initProperties( ) {
			$props = $this->getSchemaProp( 'properties' );

			foreach( $this->parents as $parent ) {
				$this->props = $this->props + $parent->properties( );
			}

			foreach( $props as $name => $prop ) {
				$this->props[ $name ] = new EntityProperty(
					$name,
					$prop[ 'label' ] ?? null,
					$prop[ 'description' ] ?? null,
					$prop[ 'type' ] ?? null,
				);
			}
		}

		/**
		 * @param string $prop
		 * @return array
		 */
		protected function getSchemaProp( string $prop ) {
			$value = $this->schema[ $prop ] ?? [ ];
			return is_array( $value ) ? $value : [ $value ];
		}

		/**
		 * Returns list of extended schemas
		 * @return array
		 */
		public function extends( ) {
			return $this->getSchemaProp( 'extends' );
		}

		/**
		 * Returns all entity properties
		 * @return EntityProperty[]
		 */
		public function properties( ) {
			return $this->props;
		}

		/**
		 * Return property label
		 * @param string $property
		 * @return string
		 */
		public function getPropertyLabel( string $property ) : string {
			return $this->props[ $property ]->label ?? $property;
		}

		/**
		 * @param string $property
		 * @return string|null
		 */
		public function getPropertyDescription( string $property ) : ?string {
			return $this->props[ $property ]->description ?? null;
		}

		/**
		 * @param string $property
		 * @return string|null
		 */
		public function getPropertyType( string $property ) : ?string {
			return $this->props[ $property ]->type ?? null;
		}

		/**
		 * Set multiple properties
		 * @param array $properties
		 * @return $this
		 */
		public function setValues( $properties ) {
			foreach( $properties as $prop => $val ) {
				$this->set( $prop, $val );
			}

			return $this;
		}

		/**
		 * Set (overwrite) property value
		 * @param string $prop
		 * @param string|array $val
		 * @return $this
		 */
		public function set( string $prop, $val ) {
			if( !is_array( $val ) ) {
				$val = [ $val ];
			}

			$this->values[ $prop ] = $val;
			return $this;
		}

		/**
		 * Append value to property
		 * @param string $prop
		 * @param string|array $val
		 * @return $this
		 */
		public function append( string $prop, $val ) {
			if( !is_array( $val ) ) {
				$val = [ $val ];
			}

			if( !isset( $this->values[ $prop ] ) ) {
				$this->values[ $prop ] = $val;
			} else {
				$this->values[ $prop ] = array_merge( $this->values[ $prop ], $val );
			}

			return $this;
		}

		/**
		 * @param string|null $id
		 * @return $this
		 */
		public function setId( ?string $id ) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @return string
		 */
		public function getSchema( ) {
			return $this->entity;
		}

		/**
		 * Return entity id
		 * @return string|null
		 */
		public function getId( ) {
			return $this->id;
		}

		/**
		 * Return property value by it's name
		 * @param string $prop
		 * @return mixed|null
		 */
		public function get( string $prop ) {
			return $this->values[ $prop ] ?? null;
		}

		/**
		 * Returns all property values
		 * @return array
		 */
		public function values( ) {
			return array_map( function ( $val ) {
				return is_array( $val ) ? $val : [ $val ];
			}, $this->values);
		}
	}