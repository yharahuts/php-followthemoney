<?php
namespace Tests\FollowTheMoney\Tests;

use FollowTheMoney\EntitySchema;
use PHPUnit\Framework\TestCase;
use Tests\Support\SchemaRepositoryAware;

/**
 * @internal
 */
class EntityFeaturedPropertiesTest extends TestCase {
	use SchemaRepositoryAware;

	/**
	 * @dataProvider FeaturedPropertiesProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getFeaturedProperties
	 */
	public function testEntityFeaturedProperties( string $schema, array $expected_properties ) : void {
		$entity = new EntitySchema( $schema, $this->getRegistry() );

		$this->assertEquals( $entity->getFeaturedProperties(), $expected_properties );
	}

	public function FeaturedPropertiesProvider() : array {
		return [
			'Company' => ['Company', ['name', 'jurisdiction', 'registrationNumber', 'incorporationDate']],
			'Person'  => ['Person', ['name', 'nationality', 'birthDate']],
			'Address' => ['Address', ['full', 'city', 'street', 'country']],
		];
	}
}
