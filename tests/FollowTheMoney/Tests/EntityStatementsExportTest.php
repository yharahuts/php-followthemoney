<?php
namespace Tests\FollowTheMoney\Tests;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Statements\EntityStatementsGenerator;
use PHPUnit\Framework\TestCase;
use Tests\Support\SchemaRepositoryAware;

/**
 * @covers \FollowTheMoney\Statements\EntityStatementsGenerator::unpack
 *
 * @internal
 */
class EntityStatementsExportTest extends TestCase {
	use SchemaRepositoryAware;

	public function testEntityIsCreatedAndExportedToStatements() {
		$entity = new EntitySchema( 'Person', $this->getRegistry() );

		$entity->set( 'firstName', 'Ivan' );
		$entity->set( 'lastName', 'Sraka' );
		$entity->set( 'birthDate', [ '1975', '1975-01-02' ] );
		$entity->setId( 'foo' );

		$generator = new EntityStatementsGenerator();
		$statements = $generator->unpack( $entity )->toArray();

		$this->assertSame( [
			[ 'id' => '595e95b7b194ccd6cb54ab64aae7c7a0a7a05e94', 'entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'firstName', 'val' => 'Ivan' ],
			[ 'id' => 'a106fae9c90af0d595cd6f7eabe3c23d3ab3cee4', 'entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'lastName', 'val' => 'Sraka' ],
			[ 'id' => '0c99969d46e26080e2117cc8c5eb89363c51a8e5', 'entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'birthDate', 'val' => '1975' ],
			[ 'id' => '834876cad0fde14fa1c5a5412d970e70b08ca8eb', 'entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'birthDate', 'val' => '1975-01-02' ],
		], $statements );
	}
}
