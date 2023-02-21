<?php
namespace FollowTheMoney\Tests;

use FollowTheMoney\EntitySchema;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class EntitySchemaTest extends TestCase {
	/**
	 * @covers \FollowTheMoney\EntitySchema::__construct
	 * @covers \FollowTheMoney\EntitySchema::properties
	 */
	public function testEntityCreation() {
		$entity = new EntitySchema( 'Company', 'followthemoney/followthemoney/schema/' );
		$this->assertIsArray( $entity->properties() );
	}

	/**
	 * @covers \FollowTheMoney\EntitySchema::append
	 * @covers \FollowTheMoney\EntitySchema::get
	 * @covers \FollowTheMoney\EntitySchema::set
	 * @covers \FollowTheMoney\EntitySchema::setId
	 */
	public function testModify() {
		$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"name": ["foo"]}, "schema": "Company"}';
		$entity = EntitySchema::fromJson( $json, 'followthemoney/followthemoney/schema/' );

		$entity->set( 'name', 'bar' );
		$this->assertEquals( [ 'bar' ], $entity->get( 'name' ) );

		$entity->set( 'name', [ 'baz' ] );
		$this->assertEquals( [ 'baz' ], $entity->get( 'name' ) );

		$entity->append( 'name', [ 'qux', 'mux' ] );
		$this->assertEquals( [ 'baz', 'qux', 'mux' ], $entity->get( 'name' ) );

		$entity->setId( 'foobar' );
		$this->assertEquals( 'foobar', $entity->getId() );
	}

	/**
	 * @covers \FollowTheMoney\EntitySchema::count
	 * @covers \FollowTheMoney\EntitySchema::getIterator
	 */
	public function testEntitySyntaxSugar() {
		$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"addressEntity": ["e89d23f9af7daa0cc6d11a5701d23cbc9084a444"], "country": ["ua"], "incorporationDate": ["1990"], "jurisdiction": ["ua"], "mainCountry": ["ua"], "name": ["\u0412\u0420\u0423", "\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430 \u0420\u0430\u0434\u0430 \u0423\u043a\u0440\u0430\u0457\u043d\u0438"], "phone": ["+380442554246"], "taxNumber": ["20064120"], "website": ["https://www.rada.gov.ua/"], "wikipediaUrl": ["https://uk.wikipedia.org/wiki/\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430_\u0420\u0430\u0434\u0430_\u0423\u043a\u0440\u0430\u0457\u043d\u0438"]}, "schema": "PublicBody"}';
		$entity = EntitySchema::fromJson( $json, 'followthemoney/followthemoney/schema/' );

		// test iterator
		$arr = iterator_to_array( $entity );
		$this->assertArrayHasKey( 'addressEntity', $arr );
		$this->assertEquals( [ 'ua' ], $arr[ 'country' ] );

		// test countable
		$this->assertEquals( 10, $entity->count() );

		// test array access
		$this->assertEquals( [ 'ua' ], $entity[ 'country' ] );
		$this->assertEquals( [ 'e89d23f9af7daa0cc6d11a5701d23cbc9084a444' ], $entity[ 'addressEntity' ] );

		unset( $entity[ 'addressEntity' ] );
		$this->assertFalse( isset( $entity[ 'addressEntity' ] ) );

		$entity[ 'addressEntity' ] = 'foobar';
		$this->assertEquals( [ 'foobar' ], $entity[ 'addressEntity' ] );
	}
}
