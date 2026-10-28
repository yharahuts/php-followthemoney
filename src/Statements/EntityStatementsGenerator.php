<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Schema\SchemaRegistry;

class EntityStatementsGenerator {
	public function __construct(
		private SchemaRegistry $registry,
		private EntityStatementFactory $factory,
	) {}

	public function unpack( EntitySchema $entity ) : EntityStatementBag {
		$statements = new EntityStatementBag();

		foreach ( $entity->values() as $name => $values ) {
			foreach ( $values as $val ) {
				$statements->append( $this->factory->create(
					entity_id: $entity->getId(),
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

		// todo: use canonical_id in case of merged entities ?
		$id = $bag[ 0 ]->entity_id;
		$entity->setId( $id );

		foreach ( $bag as $statement ) {
			/** @var EntityStatement $statement */
			$entity->append( $statement->prop, $statement->val );
		}

		return $entity;
	}
}
