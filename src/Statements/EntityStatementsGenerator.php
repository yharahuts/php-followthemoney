<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Schema\SchemaRegistry;

class EntityStatementsGenerator {
	/**
	 * @param bool $use_id_statement Add an entity id statement on unpack, take the entity id from it on pack.
	 *                               When disabled, id statements are ignored on pack.
	 */
	public function __construct(
		private SchemaRegistry $registry,
		private EntityStatementFactory $factory,
		private bool $use_id_statement = true,
	) {}

	public function unpack( EntitySchema $entity ) : EntityStatementBag {
		$statements = [];
		$id = $entity->getId();
		$schema = $entity->getSchemaName();

		if ( $this->use_id_statement ) {
			$statements[ ] = $this->factory->create(
				entity_id: $id,
				schema: $schema,
				prop: EntityStatement::ID_PROP,
				val: $id,
			);
		}

		foreach ( $entity->values() as $name => $values ) {
			foreach ( $values as $val ) {
				$statements[ ] = $this->factory->create(
					entity_id: $id,
					schema: $schema,
					prop: $name,
					val: $val,
				);
			}
		}

		return new EntityStatementBag( $statements );
	}

	/**
	 * @throws StatementException
	 */
	public function pack( EntityStatementBag $bag ) : EntitySchema {
		if ( !$bag->count() ) {
			throw new StatementException( 'Empty statements list' );
		}

		// todo: find the most specific entity schema among all statements,
		//       e.g. having statements for Thing, LegalEntity and Company, we need to create a Company entity
		$schema = $bag[ 0 ]->schema;
		$entity = new EntitySchema( $schema, $this->registry );

		$id = $this->getEntityId( $bag );
		$entity->setId( $id );
		$values = [];

		foreach ( $bag as $statement ) {
			if ( $statement->entity_id !== $id ) {
				throw new StatementException( "Statement {$statement->id} belongs to entity {$statement->entity_id}, expected {$id}" );
			}

			if ( $statement->prop === EntityStatement::ID_PROP ) {
				if ( $this->use_id_statement && $statement->val !== $id ) {
					throw new StatementException( "Id statement {$statement->id} has value {$statement->val}, expected {$id}" );
				}

				continue;
			}

			$values[ $statement->prop ][ ] = $statement->val;
		}

		return $entity->setValues( $values );
	}

	/**
	 * Entity id is taken from the id statement (if enabled), or from the first statement if there's none.
	 */
	private function getEntityId( EntityStatementBag $bag ) : string {
		// todo: use canonical_id in case of merged entities?
		if ( $this->use_id_statement ) {
			foreach ( $bag as $statement ) {
				if ( $statement->prop === EntityStatement::ID_PROP ) {
					return $statement->val;
				}
			}
		}

		return $bag[ 0 ]->entity_id;
	}
}
