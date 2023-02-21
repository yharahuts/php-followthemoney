<?php
namespace Tests\FollowTheMoney\Tests;

use FollowTheMoney\EntitySchema;
use PHPUnit\Framework\TestCase;
use Tests\Support\SchemaRepositoryAware;

/**
 * @internal
 */
final class EntityPropertiesTest extends TestCase {
	use SchemaRepositoryAware;

	public function LabelDataProvider() {
		return [
			[ 'id', 'id' ],
			[ 'registrationNumber', 'Registration number' ],
			'non-existent property' => [ 'foobar', 'foobar' ],
		];
	}

	/**
	 * @dataProvider LabelDataProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getPropertyLabel()
	 */
	public function testEntityPropertyLabels( string $property, string $label ) {
		$entity = new EntitySchema( 'Company', $this->getRegistry() );

		$actual_label = $entity->getPropertyLabel( $property );
		$this->assertEquals( $label, $actual_label );
	}

	public function DescriptionsDataProvider() {
		return [
			[ 'voenCode', 'Azerbaijan taxpayer ID' ],
			'property with no description' => [ 'registrationNumber', null ],
			'non-existent property'        => [ 'foobar', null ],
		];
	}

	/**
	 * @dataProvider DescriptionsDataProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getPropertyDescription()
	 */
	public function testEntityPropertyDescriptions( string $property, ?string $descr ) {
		$entity = new EntitySchema( 'Company', $this->getRegistry() );

		$actual_descr = $entity->getPropertyDescription( $property );
		$this->assertEquals( $descr, $actual_descr );
	}

	public function TypesDataProvider() {
		return [
			[ 'nationality', 'country' ],
			[ 'birthDate', 'date' ],
			'no property type is set' => [ 'secondName', null ],
			'non-existent property'   => [ 'foobar', null ],
		];
	}

	/**
	 * @dataProvider TypesDataProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getPropertyType()
	 */
	public function testEntityPropertyTypes( string $property, ?string $type ) {
		$entity = new EntitySchema( 'Person', $this->getRegistry() );

		$actual_type = $entity->getPropertyType( $property );
		$this->assertEquals( $type, $actual_type );
	}

	public function SchemaNameDataProvider() {
		return [
			'company'      => [ 'Company', 'Company', 'Company' ],
			'bank account' => [ 'BankAccount', 'BankAccount', 'Bank account' ],
		];
	}

	/**
	 * @dataProvider SchemaNameDataProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getSchemaLabel()
	 * @covers \FollowTheMoney\EntitySchema::getSchemaName()
	 */
	public function testEntitySchemaName( string $entity, string $schema_name, string $schema_label ) {
		$entity = new EntitySchema( $entity, $this->getRegistry() );

		$this->assertSame( $schema_name, $entity->getSchemaName() );
		$this->assertSame( $schema_label, $entity->getSchemaLabel() );
	}
}
