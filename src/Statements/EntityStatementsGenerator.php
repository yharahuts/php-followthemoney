<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Schema\SchemaRegistry;

class EntityStatementsGenerator {
	/**
	 * @param bool $use_id_statement Add entity id statement on unpack, take entity id from it on pack.
	 *                               When disabled, id statements are ignored on pack.
	 */
	public function __construct(
		private SchemaRegistry $registry,
		private EntityStatementFactory $factory,
		private bool $use_id_statement = true,
	) {}

	public function unpack( EntitySchema $entity ) : EntityStatementBag {
		$statements = new EntityStatementBag();
		$id = $entity->getId();

		if ( $this->use_id_statement ) {
			$statements->append( $this->factory->create(
				entity_id: $id,
				schema: $entity->getSchemaName(),
				prop: EntityStatement::ID_PROP,
				val: $id,
			) );
		}

		foreach ( $entity->values() as $name => $values ) {
			foreach ( $values as $val ) {
				$statements->append( $this->factory->create(
					entity_id: $id,
					schema: $entity->getSchemaName(),
					prop: $name,
					val: $val,
				) );
			}
		}

		return $statements;
	}

	/**
	 * @throws StatementException
	 */
	public function pack( EntityStatementBag $bag ) : EntitySchema {
		if ( !$bag->count() ) {
			throw new StatementException( 'Empty statements list' );
		}

		// todo: find most top-level entity schema for all statements,
		//       i.e. having statements for Thing, LegalEntity and Company, we need to create a Company entity
		$schema = $bag[ 0 ]->schema;
		$entity = new EntitySchema( $schema, $this->registry );

		$id = $this->getEntityId( $bag );
		$entity->setId( $id );

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

			$entity->append( $statement->prop, $statement->val );
		}

		return $entity;
	}

	/**
	 * Entity id is taken from id statement (if enabled), or from the first statement if there's none.
	 */
	private function getEntityId( EntityStatementBag $bag ) : string {
		// todo: use canonical_id in case of merged entities ?
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
