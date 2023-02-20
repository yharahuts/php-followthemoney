<?php
namespace FollowTheMoney;

use FollowTheMoney\Exceptions\FtmException;

trait EntitySchemaFromToTrait {
	/** {@inheritDoc} */
	public function __toString() {
		return $this->toJson();
	}

	/**
	 * Init entity from json.
	 *
	 * @param string $json
	 * @param string $dir
	 *
	 * @return static
	 *
	 * @throws FtmException
	 */
	public static function fromJson( string $json, string $dir ) : static {
		$array = json_decode( $json, true );

		if ( $array === null ) {
			$error = json_last_error_msg();

			throw new FtmException( "Can't decode json: {$error}" );
		}

		if ( !is_array( $array ) ) {
			throw new FtmException( 'Decoded json is a scalar value' );
		}

		return static::fromArray( $array, $dir );
	}

	/**
	 * @param array $array
	 * @param string $dir
	 */
	public static function fromArray( array $array, string $dir ) : static {
		if ( !isset( $array[ 'schema' ] ) ) {
			throw new FtmException( 'No schema property in array' );
		}

		if ( !isset( $array[ 'id' ] ) ) {
			throw new FtmException( 'No id property in array' );
		}

		if ( !isset( $array[ 'properties' ] ) ) {
			throw new FtmException( 'No properties in array' );
		}

		$entity = new static( $array[ 'schema' ], $dir );
		$entity->setId( $array[ 'id' ] );
		$entity->setValues( $array[ 'properties' ] );

		return $entity;
	}

	/**
	 * Save entity as array.
	 *
	 * @return array
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
	 *
	 * @param int $flags
	 *
	 * @return string
	 */
	public function toJson( $flags = JSON_UNESCAPED_UNICODE ) : string {
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
