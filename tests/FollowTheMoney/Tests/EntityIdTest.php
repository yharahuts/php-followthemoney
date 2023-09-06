<?php
namespace Tests\FollowTheMoney\Tests;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\FtmException;
use FollowTheMoney\IdGenerator\EntityIdGenerator;
use PHPUnit\Framework\TestCase;
use Tests\Support\SchemaRepositoryAware;

/**
 * @covers \FollowTheMoney\EntitySchema
 * @covers \FollowTheMoney\IdGenerator\EntityIdGenerator
 *
 * @internal
 */
class EntityIdTest extends TestCase {
	use SchemaRepositoryAware;

	public function testGettingIdFromEmptyEntityThrowsException() {
		$entity = new EntitySchema( 'Company', $this->getRegistry() );

		$this->expectException( FtmException::class );
		$entity->getId();
	}

	public function testEmptyEntityWithSetIdWontThrowException() {
		$entity = new EntitySchema( 'Company', $this->getRegistry() );
		$entity->setId( 'foo' );
		$this->assertSame( 'foo', $entity->getId() );
	}

	/**
	 * @dataProvider EntityIdProvider
	 */
	public function testIdIsGeneratedForEntity( string $schema, array $entity_props, array $id_props, string $expected_id ) {
		$entity = new EntitySchema( $schema, $this->getRegistry() );

		foreach ( $entity_props as $prop => $values ) {
			$entity->append( $prop, $values );
		}

		$id_generator = new EntityIdGenerator( $id_props );
		$entity->setIdGenerator( $id_generator );
		$actual_id = $entity->getId();

		$this->assertSame( $actual_id, $expected_id );
	}

	public function testStaticEntityIdOverridesGeneratedId() {
		$entity = new EntitySchema( 'Person', $this->getRegistry() );
		$entity->setIdGenerator( new EntityIdGenerator( [ 'name' ] ) );
		$entity->set( 'name', 'Ivan' );

		$entity->setId( 'foo' );
		$this->assertSame( 'foo', $entity->getId() );

		$entity->setId( null );
		$this->assertSame( 'eb9c234e452f9a84f8407c07da4e2b71a5bd59a0', $entity->getId() );
	}

	public function EntityIdProvider() : \Generator {
		yield [
			'schema'      => 'Person',
			'values'      => [ 'name' => 'Ivan Sraka' ],
			'id_props'    => [ 'name' ],
			'expected_id' => '3695c2f44b09f19dad7c39827ab3390306deb188',
		];

		yield [
			'schema'      => 'Person',
			'values'      => [ 'name' => [ 'Ivan Sraka' ] ],
			'id_props'    => [ 'name' ],
			'expected_id' => '3695c2f44b09f19dad7c39827ab3390306deb188',
		];

		yield [
			'schema'      => 'Person',
			'values'      => [ 'name' => [ 'Sraka Ivan' ] ],
			'id_props'    => [ 'name' ],
			'expected_id' => 'd979b679928dae570b5d6a0069353567d9f6aa84',
		];

		yield [
			'schema'      => 'Person',
			'values'      => [ 'name' => [ 'Ivan Sraka', 'Sraka Ivan' ] ],
			'id_props'    => [ 'name' ],
			'expected_id' => 'd931f9d311470055364f6f0f79e80b2b439914a0',
		];

		yield [
			'schema'      => 'Company',
			'values'      => [ 'name' => 'Ivan Sraka' ],
			'id_props'    => [ 'name' ],
			'expected_id' => '25ec8d369b386b243c3d6d50a219bad17ed4e4f7',
		];

		yield [
			'schema'      => 'Person',
			'values'      => [ 'name' => 'Ivan Sraka', 'birthDate' => '1995' ],
			'id_props'    => [ 'name' ],
			'expected_id' => '3695c2f44b09f19dad7c39827ab3390306deb188',
		];

		yield [
			'schema'      => 'Person',
			'values'      => [ 'name' => 'Ivan Sraka', 'birthDate' => '1995' ],
			'id_props'    => [ 'name', 'birthDate' ],
			'expected_id' => '00bbc613fe8adc440feee4fe81941c79ec34c912',
		];

		yield [
			'schema'      => 'Person',
			'values'      => [ 'name' => 'Ivan Sraka', 'birthDate' => '1995' ],
			'id_props'    => [ 'birthDate', 'name' ],
			'expected_id' => '00bbc613fe8adc440feee4fe81941c79ec34c912',
		];

		yield [
			'schema'      => 'Person',
			'values'      => [ 'birthDate' => '1995', 'name' => 'Ivan Sraka' ],
			'id_props'    => [ 'birthDate', 'name' ],
			'expected_id' => '00bbc613fe8adc440feee4fe81941c79ec34c912',
		];

		yield [
			'schema'      => 'Person',
			'values'      => [ 'name' => 'Ivan Sraka' ],
			'id_props'    => [ 'name', 'birthDate' ],
			'expected_id' => '3695c2f44b09f19dad7c39827ab3390306deb188',
		];
	}
}
