<?php
	namespace FollowTheMoney\Statements;

	use FollowTheMoney\Exceptions\FtmException;
	use FollowTheMoney\Exceptions\StatementException;

	/**
	 * Class EntityStatement
	 * @package FollowTheMoney\Statements
	 *
	 * @property-read string $entity_id
	 * @property-read string $schema;
	 * @property-read string $prop;
	 * @property-read string $val;
	 */
	class EntityStatement implements \JsonSerializable {
		/** @var string */
		protected string $entity_id;
		/** @var string */
		protected string $schema;
		/** @var string */
		protected string $prop;
		/** @var string */
		protected string $val;

		/**
		 * @param string $entity_id
		 * @return $this
		 */
		public function setId( string $entity_id ) {
			$this->entity_id = $entity_id;
			return $this;
		}

		/**
		 * @param string $schema
		 * @return $this
		 */
		public function setSchema( string $schema ) {
			$this->schema = $schema;
			return $this;
		}

		/**
		 * @param $prop
		 * @return $this
		 */
		public function setProp( $prop ) {
			$this->prop = $prop;
			return $this;
		}

		/**
		 * @param $val
		 * @return $this
		 */
		public function setValue( $val ) {
			$this->val = $val;
			return $this;
		}

		/**
		 * @param string $name
		 * @return mixed
		 */
		public function __get( $name ) {
			// todo
			return $this->$name;
		}

		/**
		 * @return array
		 */
		public function toArray( ) {
			return [
				'entity_id' => $this->entity_id,
				'schema'    => $this->schema,
				'prop'      => $this->prop,
				'val'       => $this->val,
			];
		}

		/**
		 * @param int $flags
		 * @return string
		 */
		public function toJson( $flags = JSON_UNESCAPED_UNICODE ) {
			return json_encode( $this->toArray( ), $flags );
		}

		/** @inheritDoc */
		public function jsonSerialize( ) {
			return $this->toArray( );
		}

		/**
		 * @param string $json
		 * @return EntityStatement
		 * @throws StatementException
		 */
		public static function fromJson( string $json ) {
			$array = json_decode( $json, true );

			if( json_last_error( ) !== 0 ) {
				throw new StatementException( "Failed to parse json: " . json_last_error_msg( ) );
			}

			return static::fromArray( $array );
		}

		/**
		 * @param array $array
		 * @return EntityStatement
		 * @throws StatementException
		 */
		public static function fromArray( array $array ) {
			$item = new static( );

			try {
				return $item
					->setId( $array[ 'entity_id' ] )
					->setValue( $array[ 'val' ] )
					->setProp( $array[ 'prop' ] )
					->setSchema( $array[ 'schema' ] );
			} catch ( \Throwable $e ) {
				throw new StatementException( "Failed to init statement: " . $e->getMessage( ), 0, $e );
			}
		}
	}