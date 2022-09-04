<?php
	namespace FollowTheMoney\Statements;

	use FollowTheMoney\EntitySchema;
	use FollowTheMoney\Exceptions\StatementException;

	class EntityStatementsGenerator {
		/**
		 * @param EntitySchema $entity
		 * @return EntityStatementBag
		 */
		public function unpack( EntitySchema $entity ) {
			$statements = new EntityStatementBag( );

			foreach( $entity->values( ) as $name => $values ) {
				foreach( $values as $val ) {
					$statements->add( )
						->setId( $entity->getId( ) )
						->setSchema( $entity->getSchema( ) )
						->setProp( $name )
						->setValue( $val );
				}
			}

			return $statements;
		}


		/**
		 * @param EntityStatementBag $bag
		 * @param string $dir
		 * @throws StatementException
		 */
		public function pack( EntityStatementBag $bag, string $dir ) {
			if( !$bag->count( ) ) {
				throw new StatementException( "Empty statements list" );
			}

			// todo: find most top-level entity schema for all statements,
			//       i.e. having statements for Thing, LegalEntity and Company, we need to create a Company entity
			$schema = $bag[ 0 ]->schema;
			$entity = new EntitySchema( $schema, $dir );

			// todo: use canonical_id in case of merged entities
			$id = $bag[ 0 ]->entity_id;
			$entity->setId( $id );

			foreach( $bag as $statement ) {
				/** @var EntityStatement $statement */
				$entity->append( $statement->prop, $statement->val );
			}

			return $entity;
		}
	}