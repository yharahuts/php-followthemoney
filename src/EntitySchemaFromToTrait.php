<?php
	namespace FollowTheMoney;

	use FollowTheMoney\Exceptions\FtmException;

	trait EntitySchemaFromToTrait {
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