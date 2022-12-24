<?php
	namespace FollowTheMoney\Tests;

	use FollowTheMoney\EntitySchema;
	use PHPUnit\Framework\TestCase;

	class EntityFeaturedPropertiesTest extends TestCase {
		public function FeaturedPropertiesProvider( ) {
			return [
				'Company' => [ 'Company', [ 'name', 'jurisdiction', 'registrationNumber', 'incorporationDate' ] ],
				'Person'  => [ 'Person', [ 'name', 'nationality', 'birthDate' ] ],
				'Address' => [ 'Address', [ 'full', 'city', 'street', 'country' ] ],
			];
		}

		/**
		 * @dataProvider FeaturedPropertiesProvider
		 * @covers \FollowTheMoney\EntitySchema::getFeaturedProperties
		 */
		public function testEntityFeaturedProperties( string $schema, array $expected_properties ) {
			$entity = new EntitySchema( $schema, 'followthemoney/followthemoney/schema/' );

			$this->assertEquals( $entity->getFeaturedProperties( ), $expected_properties );
		}
	}