<?php
	namespace FollowTheMoney;

	use FollowTheMoney\Exceptions\FtmException;

	class EntitySchema implements \JsonSerializable {
		/** @var string */
		protected string $dir;
		/** @var string  */
		protected string $entity;
		/** @var object */
		protected $schema;

		/** @var EntitySchema[] */
		protected $parents = [ ];
		/** @var EntityProperty[ ] */
		protected $props = [ ];

		/** @var string|null */
		protected ?string $id = null;
		/** @var array */
		protected $values = [ ];

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
				$this->props[ $name ] = new EntityProperty( $name, $prop[ 'label' ] ?? null );
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

		/**
		 * Init entity from json
		 * @param string $json
		 * @param string $dir
		 * @return static
		 * @throws FtmException
		 */
		public static function fromJson( string $json, string $dir ) {
			$array = json_decode( $json, true );
			return static::fromArray( $array, $dir );
		}

		/**
		 * @param array $array
		 * @param string $dir
		 */
		public static function fromArray( array $array, string $dir ) {
			if( !isset( $array[ 'schema' ] ) ) {
				throw new FtmException( "No schema property in json" );
			}

			$entity = new static( $array[ 'schema' ], $dir );
			$entity->setId( $array[ 'id' ] );
			$entity->setValues( $array[ 'properties' ] );

			return $entity;
		}

		/**
		 * Save entity as array
		 * @return array
		 */
		public function toArray( ) {
			return [
				'id'         => $this->getId( ),
				'schema'     => $this->entity,
				'properties' => $this->values( ),
			];
		}

		/**
		 * Save entity as json string
		 * @param int $flags
		 * @return string|false
		 */
		public function toJson( $flags = JSON_UNESCAPED_UNICODE ) {
			$array = $this->toArray( );
			return json_encode( $array, $flags );
		}

		/** @inheritDoc */
		public function jsonSerialize( ) {
			return $this->toArray( );
		}

		/** @inheritDoc */
		public function __toString( ) {
			return $this->toJson( );
		}
	}