<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\EntityProperty;
use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Exceptions\UnknownPropertyException;
use FollowTheMoney\IdGenerator\StatementIdGenerator;
use FollowTheMoney\PropertyType;
use FollowTheMoney\Schema\SchemaRegistry;

class EntityStatementFactory {
	/** @var array<string, array<string, PropertyType>> */
	private array $types = [];

	public function __construct(
		private SchemaRegistry $registry,
		private StatementIdGenerator $id_generator,
	) {}

	/**
	 * @throws UnknownPropertyException
	 */
	public function create( string $entity_id, string $schema, string $prop, string $val ) : EntityStatement {
		return new EntityStatement(
			id: $this->id_generator->generate( $entity_id, $schema, $prop, $val ),
			entity_id: $entity_id,
			schema: $schema,
			prop: $prop,
			prop_type: $this->types[ $schema ][ $prop ] ?? $this->getPropertyType( $schema, $prop ),
			val: $val,
		);
	}

	/**
	 * Statement id is taken from the array, or generated if it's empty.
	 * Property type is always taken from schema.
	 *
	 * @throws StatementException
	 */
	public function fromArray( array $array ) : EntityStatement {
		try {
			$entity_id = self::stringValue( $array[ 'entity_id' ] ?? null, 'entity_id' );
			$schema = self::stringValue( $array[ 'schema' ] ?? null, 'schema' );
			$prop = self::stringValue( $array[ 'prop' ] ?? null, 'prop' );
			$val = self::stringValue( $array[ 'val' ] ?? null, 'val' );

			if ( empty( $array[ 'id' ] ) ) {
				return $this->create( $entity_id, $schema, $prop, $val );
			}

			return new EntityStatement(
				id: self::stringValue( $array[ 'id' ], 'id' ),
				entity_id: $entity_id,
				schema: $schema,
				prop: $prop,
				prop_type: $this->getPropertyType( $schema, $prop ),
				val: $val,
			);
		} catch ( \Throwable $e ) {
			throw new StatementException( 'Failed to init statement: '.$e->getMessage(), 0, $e );
		}
	}

	/**
	 * @throws StatementException
	 */
	public function fromJson( string $json ) : EntityStatement {
		$array = json_decode( $json, true );

		if ( json_last_error() !== 0 ) {
			throw new StatementException( 'Failed to parse json: '.json_last_error_msg() );
		}

		if ( !is_array( $array ) ) {
			throw new StatementException( 'Decoded json is a scalar value' );
		}

		return $this->fromArray( $array );
	}

	/**
	 * Scalars are cast to string, the same way PHP coerces them for string arguments.
	 *
	 * @throws StatementException
	 */
	private static function stringValue( mixed $value, string $name ) : string {
		if ( !is_scalar( $value ) ) {
			throw new StatementException( "Expected scalar {$name}, got ".get_debug_type( $value ) );
		}

		return (string) $value;
	}

	/**
	 * Property types of a schema are cached on the first call, the id statement property overrides the schema one.
	 *
	 * @throws UnknownPropertyException
	 */
	private function getPropertyType( string $schema, string $prop ) : PropertyType {
		if ( !isset( $this->types[ $schema ] ) ) {
			$properties = (new EntitySchema( $schema, $this->registry ))->properties();

			$this->types[ $schema ] = array_map( fn ( EntityProperty $property ) => $property->type, $properties );
			$this->types[ $schema ][ EntityStatement::ID_PROP ] = PropertyType::Id;
		}

		return $this->types[ $schema ][ $prop ] ?? throw new UnknownPropertyException( "No property {$prop} is defined for schema {$schema}" );
	}
}
