<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Schema\SchemaRegistryInterface;

class EntityStatementsGenerator {
	/**
	 * @param EntitySchema $entity
	 *
	 * @return EntityStatementBag
	 */
	public function unpack( EntitySchema $entity ) {
		$statements = new EntityStatementBag();

		foreach ( $entity->values() as $name => $values ) {
			foreach ( $values as $val ) {
				$statements->add()
					->setId( $entity->getId() )
					->setSchema( $entity->getSchemaName() )
					->setProp( $name )
					->setValue( $val )
				;
			}
		}

		return $statements;
	}

	/**
	 * @param EntityStatementBag $bag
	 * @param SchemaRegistryInterface $registry
	 *
	 * @return EntitySchema
	 *
	 * @throws StatementException
	 */
	public function pack( EntityStatementBag $bag, SchemaRegistryInterface $registry ) {
		if ( !$bag->count() ) {
			throw new StatementException( 'Empty statements list' );
		}

		// todo: find most top-level entity schema for all statements,
		//       i.e. having statements for Thing, LegalEntity and Company, we need to create a Company entity
		$schema = $bag[ 0 ]->getSchema();
		$entity = new EntitySchema( $schema, $registry );

		// todo: use canonical_id in case of merged entities ?
		$id = $bag[ 0 ]->getEntityId();
		$entity->setId( $id );

		foreach ( $bag as $statement ) {
			// @var EntityStatement $statement
			$entity->append( $statement->getPropertyName(), $statement->getValue() );
		}

		return $entity;
	}
}
