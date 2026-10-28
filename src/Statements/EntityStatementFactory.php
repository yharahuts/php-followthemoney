<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Exceptions\UnknownPropertyException;
use FollowTheMoney\IdGenerator\StatementIdGenerator;
use FollowTheMoney\PropertyType;
use FollowTheMoney\Schema\SchemaRegistry;

class EntityStatementFactory {
	/** @var EntitySchema[] */
	private array $schemas = [];

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
			prop_type: $this->getPropertyType( $schema, $prop ),
			val: $val,
		);
	}

	/**
	 * Statement id is taken from array, or generated if it's empty.
	 * Property type is always taken from schema.
	 *
	 * @throws StatementException
	 */
	public function fromArray( array $array ) : EntityStatement {
		try {
			if ( empty( $array[ 'id' ] ) ) {
				return $this->create( $array[ 'entity_id' ], $array[ 'schema' ], $array[ 'prop' ], $array[ 'val' ] );
			}

			return new EntityStatement(
				id: $array[ 'id' ],
				entity_id: $array[ 'entity_id' ],
				schema: $array[ 'schema' ],
				prop: $array[ 'prop' ],
				prop_type: $this->getPropertyType( $array[ 'schema' ], $array[ 'prop' ] ),
				val: $array[ 'val' ],
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

		return $this->fromArray( $array );
	}

	/**
	 * @throws UnknownPropertyException
	 */
	private function getPropertyType( string $schema, string $prop ) : PropertyType {
		$this->schemas[ $schema ] ??= new EntitySchema( $schema, $this->registry );

		if ( $prop === EntityStatement::ID_PROP ) {
			return PropertyType::Id;
		}

		$type = $this->schemas[ $schema ]->getPropertyType( $prop );

		if ( $type === null ) {
			throw new UnknownPropertyException( "No property {$prop} is defined for schema {$schema}" );
		}

		return $type;
	}
}
