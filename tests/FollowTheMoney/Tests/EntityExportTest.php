<?php
namespace Tests\FollowTheMoney\Tests;

use FollowTheMoney\EntitySchema;
use PHPUnit\Framework\TestCase;
use Tests\Support\SchemaRepositoryAware;

/**
 * @internal
 */
final class EntityExportTest extends TestCase {
	use SchemaRepositoryAware;

	/**
	 * @covers \FollowTheMoney\EntitySchema::toArray
	 */
	public function testExportEntityToArray() {
		$entity = $this->getEntity();

		$this->assertIsArray( $entity->toArray() );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $entity->toArray()[ 'id' ] );
		$this->assertEquals( [ 'foo' ], $entity->toArray()[ 'properties' ][ 'name' ] );
	}

	/**
	 * @covers \FollowTheMoney\EntitySchema::__toString
	 * @covers \FollowTheMoney\EntitySchema::jsonSerialize
	 * @covers \FollowTheMoney\EntitySchema::toJson
	 */
	public function testExportEntityToJson() {
		$entity = $this->getEntity();

		$json = json_decode( $entity->toJson() );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $json->id );
		$this->assertEquals( [ 'foo' ], $json->properties->name );

		$this->assertEquals( $entity->toJson(), (string) $entity );
		$this->assertEquals( json_encode( $entity ), (string) $entity );
	}

	protected function getEntity() : EntitySchema {
		$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"name": ["foo"]}, "schema": "Company"}';

		return EntitySchema::fromJson( $json, $this->getRegistry() );
	}
}
