<?php
namespace Tests\FollowTheMoney\Statements;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Statements\EntityStatementBag;
use FollowTheMoney\Statements\EntityStatementsGenerator;
use PHPUnit\Framework\TestCase;
use Tests\Support\SchemaRepositoryAware;

/**
 * @covers \FollowTheMoney\Statements\EntityStatement
 *
 * @internal
 */
class EntityStatementsGeneratorTest extends TestCase {
	use SchemaRepositoryAware;

	public function testEntityIsUnpackedToStatements() {
		$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"addressEntity": ["e89d23f9af7daa0cc6d11a5701d23cbc9084a444"], "country": ["ua"], "incorporationDate": ["1990"], "jurisdiction": ["ua"], "mainCountry": ["ua"], "name": ["\u0412\u0420\u0423", "\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430 \u0420\u0430\u0434\u0430 \u0423\u043a\u0440\u0430\u0457\u043d\u0438"], "phone": ["+380442554246"], "taxNumber": ["20064120"], "website": ["https://www.rada.gov.ua/"], "wikipediaUrl": ["https://uk.wikipedia.org/wiki/\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430_\u0420\u0430\u0434\u0430_\u0423\u043a\u0440\u0430\u0457\u043d\u0438"]}, "schema": "PublicBody"}';
		$entity = EntitySchema::fromJson( $json, $this->getRegistry() );

		$generator = new EntityStatementsGenerator();
		$statements = $generator->unpack( $entity );

		$this->assertTrue( is_iterable( $statements ) );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statements[ 0 ]->getEntityId() );
		$this->assertEquals( 'PublicBody', $statements[ 0 ]->getSchema() );

		$this->assertEquals( $statements->toArray()[ 0 ][ 'schema' ], 'PublicBody' );
		$this->assertEquals( $statements->toArray()[ 0 ][ 'entity_id' ], 'bea008dac1ea309d22e100ceb0a5f3a44db882fa' );

		$statement = $statements[ 0 ]->toArray();
		$this->assertEquals( [
			'id'        => 'b6355556a23cf37b316d495e4219c17ab33db1b9',
			'entity_id' => 'bea008dac1ea309d22e100ceb0a5f3a44db882fa',
			'schema'    => 'PublicBody',
			'prop'      => 'addressEntity',
			'val'       => 'e89d23f9af7daa0cc6d11a5701d23cbc9084a444',
		], $statement );

		$this->assertEquals( $statements[ 0 ]->toJson(), json_encode( $statements[ 0 ] ) );
		$this->assertEquals( json_decode( $statements[ 0 ]->toJson(), true ), $statements[ 0 ]->toArray() );
	}

	public function testStatementsArePackedToEntity() {
		$array = [
			json_decode( '{"entity_id":"foobar","schema":"PublicBody","prop":"country","val":"ua"}', true ),
			json_decode( '{"entity_id":"foobar","schema":"PublicBody","prop":"taxNumber","val":"20064120"}', true ),
			json_decode( '{"entity_id":"foobar","schema":"PublicBody","prop":"taxNumber","val":"12345678"}', true ),
		];

		$statements = EntityStatementBag::fromArray( $array );

		$generator = new EntityStatementsGenerator();
		$entity = $generator->pack( $statements, $this->getRegistry() );

		$this->assertEquals( 'foobar', $entity->getId() );
		$this->assertEquals( 'PublicBody', $entity->getSchemaName() );

		$this->assertEquals( [ '20064120', '12345678' ], $entity[ 'taxNumber' ] );
	}

	public function testPackCreatesTopMostEntitySchema() {
		$this->markTestIncomplete();
	}

	public function testPackThrowsExceptionIfCantFigureOutTheTopmostSchema() {
		$this->markTestIncomplete();
	}
}
