<?php
namespace FollowTheMoney;

use FollowTheMoney\Exceptions\FtmException;
use FollowTheMoney\Schema\SchemaRegistry;

trait EntitySchemaFromToTrait {
	/** {@inheritDoc} */
	public function __toString() : string {
		return $this->toJson();
	}

	/**
	 * Init entity from json.
	 *
	 * @throws FtmException
	 */
	public static function fromJson( string $json, SchemaRegistry $registry ) : static {
		$array = json_decode( $json, true );

		if ( $array === null ) {
			$error = json_last_error_msg();

			throw new FtmException( "Can't decode json: {$error}" );
		}

		if ( !is_array( $array ) ) {
			throw new FtmException( 'Decoded json is a scalar value' );
		}

		return static::fromArray( $array, $registry );
	}

	/**
	 * @throws FtmException
	 */
	public static function fromArray( array $array, SchemaRegistry $registry ) : static {
		if ( !isset( $array[ 'schema' ] ) ) {
			throw new FtmException( 'No schema property in array' );
		}

		if ( !isset( $array[ 'id' ] ) ) {
			throw new FtmException( 'No id property in array' );
		}

		if ( !isset( $array[ 'properties' ] ) ) {
			throw new FtmException( 'No properties in array' );
		}

		try {
			$entity = new static( $array[ 'schema' ], $registry );
			$entity->setId( $array[ 'id' ] );
			$entity->setValues( $array[ 'properties' ] );
		} catch ( \Throwable $e ) {
			throw new FtmException( "Can't create entity: ".$e->getMessage(), 0, $e );
		}

		return $entity;
	}

	/**
	 * Save entity as array.
	 */
	public function toArray() : array {
		return [
			'id'         => $this->getId(),
			'schema'     => $this->entity,
			'properties' => $this->values(),
		];
	}

	/**
	 * Save entity as json string.
	 */
	public function toJson( int $flags = JSON_UNESCAPED_UNICODE ) : string {
		$array = $this->toArray();
		$json = json_encode( $array, $flags );

		if ( $json === false ) {
			$error = json_last_error_msg();

			throw new FtmException( "Can't cast to json: {$error}" );
		}

		return $json;
	}

	/** {@inheritDoc} */
	public function jsonSerialize() : array {
		return $this->toArray();
	}
}
