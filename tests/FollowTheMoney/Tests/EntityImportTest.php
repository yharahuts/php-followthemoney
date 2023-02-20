<?php
namespace FollowTheMoney\Tests;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\FtmException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EntityImportTest extends TestCase {
	/**
	 * @covers \FollowTheMoney\EntitySchema::fromArray
	 */
	public function testImportFromArray() {
		$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"addressEntity": ["e89d23f9af7daa0cc6d11a5701d23cbc9084a444"], "country": ["ua"], "incorporationDate": ["1990"], "jurisdiction": ["ua"], "mainCountry": ["ua"], "name": ["\u0412\u0420\u0423", "\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430 \u0420\u0430\u0434\u0430 \u0423\u043a\u0440\u0430\u0457\u043d\u0438"], "phone": ["+380442554246"], "taxNumber": ["20064120"], "website": ["https://www.rada.gov.ua/"], "wikipediaUrl": ["https://uk.wikipedia.org/wiki/\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430_\u0420\u0430\u0434\u0430_\u0423\u043a\u0440\u0430\u0457\u043d\u0438"]}, "schema": "PublicBody"}';
		$arr = json_decode( $json, true );

		$entity = EntitySchema::fromArray( $arr, 'followthemoney/followthemoney/schema/' );

		$this->assertEquals( 'PublicBody', $entity->getSchemaName() );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $entity->getId() );
		$this->assertEquals( [ 'ua' ], $entity->get( 'country' ) );
	}

	public function BrokenArrayProvider() {
		return [
			[ [ ] ],
			[ [ 'schema' => 'PublicBody' ] ],
			[ [ 'schema' => 'foo bar' ] ],
			[ [ 'schema' => 'foo bar', 'id' => 'foo' ] ],
			[ [ 'schema' => 'foo bar', 'id' => 'foo', 'properties' => [ '' ] ] ],
		];
	}

	/**
	 * @covers \FollowTheMoney\EntitySchema::fromArray
	 *
	 * @dataProvider BrokenArrayProvider
	 */
	public function testImportingFromBrokenArrayThrowsException( array $broken_array ) {
		$this->expectException( FtmException::class );
		EntitySchema::fromArray( $broken_array, 'followthemoney/followthemoney/schema/' );
	}

	/**
	 * @covers \FollowTheMoney\EntitySchema::fromJson
	 */
	public function testImportFromJson() {
		$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"addressEntity": ["e89d23f9af7daa0cc6d11a5701d23cbc9084a444"], "country": ["ua"], "incorporationDate": ["1990"], "jurisdiction": ["ua"], "mainCountry": ["ua"], "name": ["\u0412\u0420\u0423", "\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430 \u0420\u0430\u0434\u0430 \u0423\u043a\u0440\u0430\u0457\u043d\u0438"], "phone": ["+380442554246"], "taxNumber": ["20064120"], "website": ["https://www.rada.gov.ua/"], "wikipediaUrl": ["https://uk.wikipedia.org/wiki/\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430_\u0420\u0430\u0434\u0430_\u0423\u043a\u0440\u0430\u0457\u043d\u0438"]}, "schema": "PublicBody"}';
		$entity = EntitySchema::fromJson( $json, 'followthemoney/followthemoney/schema/' );

		$this->assertEquals( 'PublicBody', $entity->getSchemaName() );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $entity->getId() );
		$this->assertEquals( [ 'ua' ], $entity->get( 'country' ) );

		$this->assertIsArray( $entity->values() );
		$this->assertEquals( [ 'ua' ], $entity->values()[ 'country' ] );
	}

	public function BrokenJsonProvider() : array {
		return [
			[ '{}' ],
			[ '{' ],
			[ 'null' ],
			[ 'foo bar' ],
		];
	}

	/**
	 * @covers \FollowTheMoney\EntitySchema::fromJson
	 *
	 * @dataProvider BrokenJsonProvider
	 */
	public function testImportingFromBrokenJsonThrowsException( string $json ) {
		$this->expectException( FtmException::class );
		EntitySchema::fromJson( $json, 'followthemoney/followthemoney/schema/' );
	}
}
