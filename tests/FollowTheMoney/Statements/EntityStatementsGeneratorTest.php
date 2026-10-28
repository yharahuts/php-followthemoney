<?php
namespace Tests\FollowTheMoney\Statements;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\UnknownPropertyException;
use FollowTheMoney\Statements\EntityStatementBag;
use FollowTheMoney\Statements\EntityStatementFactory;
use FollowTheMoney\Statements\EntityStatementsGenerator;
use PHPUnit\Framework\TestCase;
use Tests\Support\ReadableStatementIdGenerator;
use Tests\Support\SchemaRepositoryAware;
use Tests\Support\StatementFactoryAware;

/**
 * @covers \FollowTheMoney\Statements\EntityStatement
 *
 * @internal
 */
class EntityStatementsGeneratorTest extends TestCase {
	use SchemaRepositoryAware;
	use StatementFactoryAware;

	public function testEntityIsUnpackedToStatements() : void {
		$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"addressEntity": ["e89d23f9af7daa0cc6d11a5701d23cbc9084a444"], "country": ["ua"], "incorporationDate": ["1990"], "jurisdiction": ["ua"], "mainCountry": ["ua"], "name": ["\u0412\u0420\u0423", "\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430 \u0420\u0430\u0434\u0430 \u0423\u043a\u0440\u0430\u0457\u043d\u0438"], "phone": ["+380442554246"], "taxNumber": ["20064120"], "website": ["https://www.rada.gov.ua/"], "wikipediaUrl": ["https://uk.wikipedia.org/wiki/\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430_\u0420\u0430\u0434\u0430_\u0423\u043a\u0440\u0430\u0457\u043d\u0438"]}, "schema": "PublicBody"}';
		$registry = $this->getRegistry();
		$entity = EntitySchema::fromJson( $json, $registry );

		$generator = new EntityStatementsGenerator( $registry, $this->getStatementFactory() );
		$statements = $generator->unpack( $entity );

		$this->assertTrue( is_iterable( $statements ) );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statements[ 0 ]->entity_id );
		$this->assertEquals( 'PublicBody', $statements[ 0 ]->schema );

		$this->assertEquals( $statements->toArray()[ 0 ][ 'schema' ], 'PublicBody' );
		$this->assertEquals( $statements->toArray()[ 0 ][ 'entity_id' ], 'bea008dac1ea309d22e100ceb0a5f3a44db882fa' );

		$statement = $statements[ 0 ]->toArray();
		$this->assertEquals( [
			'id'        => 'b6355556a23cf37b316d495e4219c17ab33db1b9',
			'entity_id' => 'bea008dac1ea309d22e100ceb0a5f3a44db882fa',
			'schema'    => 'PublicBody',
			'prop'      => 'addressEntity',
			'prop_type' => 'entity',
			'val'       => 'e89d23f9af7daa0cc6d11a5701d23cbc9084a444',
		], $statement );

		$this->assertEquals( $statements[ 0 ]->toJson(), json_encode( $statements[ 0 ] ) );
		$this->assertEquals( json_decode( $statements[ 0 ]->toJson(), true ), $statements[ 0 ]->toArray() );
	}

	public function testEntityIsUnpackedWithCustomStatementIdGenerator() : void {
		$registry = $this->getRegistry();
		$entity = new EntitySchema( 'Person', $registry );
		$entity->setId( 'foo' );
		$entity->set( 'firstName', 'Ivan' );
		$entity->set( 'birthDate', ['1975', '1975-01-02'] );

		$generator = new EntityStatementsGenerator( $registry, new EntityStatementFactory( $registry, new ReadableStatementIdGenerator() ) );
		$statements = $generator->unpack( $entity );

		$this->assertSame(
			['Person:foo:firstName:Ivan', 'Person:foo:birthDate:1975', 'Person:foo:birthDate:1975-01-02'],
			array_column( $statements->toArray(), 'id' ),
		);
	}

	public function testUnpackingUnknownPropertyThrowsException() : void {
		$registry = $this->getRegistry();
		$entity = new EntitySchema( 'Person', $registry );
		$entity->setId( 'foo' );
		$entity->set( 'foobar', 'Ivan' );

		$generator = new EntityStatementsGenerator( $registry, $this->getStatementFactory() );

		$this->expectException( UnknownPropertyException::class );
		$generator->unpack( $entity );
	}

	public function testStatementsArePackedToEntity() : void {
		$array = [
			json_decode( '{"entity_id":"foobar","schema":"PublicBody","prop":"country","val":"ua"}', true ),
			json_decode( '{"entity_id":"foobar","schema":"PublicBody","prop":"taxNumber","val":"20064120"}', true ),
			json_decode( '{"entity_id":"foobar","schema":"PublicBody","prop":"taxNumber","val":"12345678"}', true ),
		];

		$statements = EntityStatementBag::fromArray( $array, $this->getStatementFactory() );

		$generator = new EntityStatementsGenerator( $this->getRegistry(), $this->getStatementFactory() );
		$entity = $generator->pack( $statements );

		$this->assertEquals( 'foobar', $entity->getId() );
		$this->assertEquals( 'PublicBody', $entity->getSchemaName() );

		$this->assertEquals( ['20064120', '12345678'], $entity[ 'taxNumber' ] );
	}

	public function testPackCreatesTopMostEntitySchema() : void {
		$this->markTestIncomplete();
	}

	public function testPackThrowsExceptionIfCantFigureOutTheTopmostSchema() : void {
		$this->markTestIncomplete();
	}
}
