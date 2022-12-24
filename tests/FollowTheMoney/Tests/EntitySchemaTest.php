<?php
	namespace FollowTheMoney\Tests;

	use FollowTheMoney\Exceptions\FtmException;
	use PHPUnit\Framework\TestCase;
	use FollowTheMoney\EntitySchema;

	class EntitySchemaTest extends TestCase {
		/**
		 * @covers \FollowTheMoney\EntitySchema::__construct
		 * @covers \FollowTheMoney\EntitySchema::properties
		 */
		public function test_creation( ) {
			$entity = new EntitySchema( 'Company', 'followthemoney/followthemoney/schema/' );
			$this->assertIsArray( $entity->properties( ) );
		}

		/**
		 * @covers \FollowTheMoney\EntitySchema::fromJson
		 */
		public function test_broken_json( ) {
			$this->expectException( FtmException::class );
			$entity = EntitySchema::fromJson( '{}', 'followthemoney/followthemoney/schema/' );
		}

		/**
		 * @covers \FollowTheMoney\EntitySchema::fromJson
		 * @covers \FollowTheMoney\EntitySchema::getSchema
		 * @covers \FollowTheMoney\EntitySchema::getId
		 * @covers \FollowTheMoney\EntitySchema::get
		 */
		public function test_json_parsing( ) {
			$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"addressEntity": ["e89d23f9af7daa0cc6d11a5701d23cbc9084a444"], "country": ["ua"], "incorporationDate": ["1990"], "jurisdiction": ["ua"], "mainCountry": ["ua"], "name": ["\u0412\u0420\u0423", "\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430 \u0420\u0430\u0434\u0430 \u0423\u043a\u0440\u0430\u0457\u043d\u0438"], "phone": ["+380442554246"], "taxNumber": ["20064120"], "website": ["https://www.rada.gov.ua/"], "wikipediaUrl": ["https://uk.wikipedia.org/wiki/\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430_\u0420\u0430\u0434\u0430_\u0423\u043a\u0440\u0430\u0457\u043d\u0438"]}, "schema": "PublicBody"}';
			$entity = EntitySchema::fromJson( $json, 'followthemoney/followthemoney/schema/' );

			$this->assertEquals( 'PublicBody', $entity->getSchema( ) );
			$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $entity->getId( ) );
			$this->assertEquals( [ 'ua' ], $entity->get( 'country' ) );

			$this->assertIsArray( $entity->values( ) );
			$this->assertEquals( [ 'ua' ], $entity->values( )[ 'country' ] );
		}

		/**
		 * @covers \FollowTheMoney\EntitySchema::fromArray
		 */
		public function test_from_array( ) {
			$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"addressEntity": ["e89d23f9af7daa0cc6d11a5701d23cbc9084a444"], "country": ["ua"], "incorporationDate": ["1990"], "jurisdiction": ["ua"], "mainCountry": ["ua"], "name": ["\u0412\u0420\u0423", "\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430 \u0420\u0430\u0434\u0430 \u0423\u043a\u0440\u0430\u0457\u043d\u0438"], "phone": ["+380442554246"], "taxNumber": ["20064120"], "website": ["https://www.rada.gov.ua/"], "wikipediaUrl": ["https://uk.wikipedia.org/wiki/\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430_\u0420\u0430\u0434\u0430_\u0423\u043a\u0440\u0430\u0457\u043d\u0438"]}, "schema": "PublicBody"}';
			$arr = json_decode( $json, true );

			$entity = EntitySchema::fromArray( $arr, 'followthemoney/followthemoney/schema/' );

			$this->assertEquals( 'PublicBody', $entity->getSchema( ) );
			$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $entity->getId( ) );
			$this->assertEquals( [ 'ua' ], $entity->get( 'country' ) );
		}

		/**
		 * @covers \FollowTheMoney\EntitySchema::fromJson
		 * @covers \FollowTheMoney\EntitySchema::get
		 * @covers \FollowTheMoney\EntitySchema::set
		 * @covers \FollowTheMoney\EntitySchema::append
		 * @covers \FollowTheMoney\EntitySchema::setId
		 */
		public function test_modify( ) {
			$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"name": ["foo"]}, "schema": "Company"}';
			$entity = EntitySchema::fromJson( $json, 'followthemoney/followthemoney/schema/' );

			$entity->set( 'name', 'bar' );
			$this->assertEquals( [ 'bar' ], $entity->get( 'name' ) );

			$entity->set( 'name', [ 'baz' ] );
			$this->assertEquals( [ 'baz' ], $entity->get( 'name' ) );

			$entity->append( 'name', [ 'qux', 'mux' ] );
			$this->assertEquals( [ 'baz', 'qux', 'mux' ], $entity->get( 'name' ) );

			$entity->setId( 'foobar' );
			$this->assertEquals( 'foobar', $entity->getId( ) );
		}

		/**
		 * @covers \FollowTheMoney\EntitySchema::toArray
		 * @covers \FollowTheMoney\EntitySchema::toJson
		 * @covers \FollowTheMoney\EntitySchema::__toString
		 */
		public function test_save( ) {
			$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"name": ["foo"]}, "schema": "Company"}';
			$entity = EntitySchema::fromJson( $json, 'followthemoney/followthemoney/schema/' );

			$this->assertIsArray( $entity->toArray( ) );
			$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $entity->toArray( )[ 'id' ] );
			$this->assertEquals( [ 'foo' ], $entity->toArray( )[ 'properties' ][ 'name' ] );

			$json = json_decode( $entity->toJson( ) );
			$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $json->id );
			$this->assertEquals( [ 'foo' ], $json->properties->name );

			$this->assertEquals( $entity->toJson( ), (string) $entity );
		}

		/**
		 * @covers \FollowTheMoney\EntitySchema::getIterator
		 * @covers \FollowTheMoney\EntitySchema::count
		 */
		public function test_sugar( ) {
			$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"addressEntity": ["e89d23f9af7daa0cc6d11a5701d23cbc9084a444"], "country": ["ua"], "incorporationDate": ["1990"], "jurisdiction": ["ua"], "mainCountry": ["ua"], "name": ["\u0412\u0420\u0423", "\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430 \u0420\u0430\u0434\u0430 \u0423\u043a\u0440\u0430\u0457\u043d\u0438"], "phone": ["+380442554246"], "taxNumber": ["20064120"], "website": ["https://www.rada.gov.ua/"], "wikipediaUrl": ["https://uk.wikipedia.org/wiki/\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430_\u0420\u0430\u0434\u0430_\u0423\u043a\u0440\u0430\u0457\u043d\u0438"]}, "schema": "PublicBody"}';
			$entity = EntitySchema::fromJson( $json, 'followthemoney/followthemoney/schema/' );

			// test iterator
			$arr = iterator_to_array( $entity );
			$this->assertArrayHasKey( 'addressEntity', $arr );
			$this->assertEquals( [ 'ua' ], $arr[ 'country' ] );

			// test countable
			$this->assertEquals( 10, $entity->count( ) );

			// test array access
			$this->assertEquals( [ 'ua' ], $entity[ 'country' ] );
			$this->assertEquals( [ 'e89d23f9af7daa0cc6d11a5701d23cbc9084a444' ], $entity[ 'addressEntity' ] );

			unset( $entity[ 'addressEntity' ] );
			$this->assertFalse( isset( $entity[ 'addressEntity' ] ) );

			$entity[ 'addressEntity' ] = 'foobar';
			$this->assertEquals( [ 'foobar' ], $entity[ 'addressEntity' ] );
		}
	}
