<?php
namespace Tests\FollowTheMoney\Tests;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\PropertyType;
use PHPUnit\Framework\TestCase;
use Tests\Support\SchemaRepositoryAware;

/**
 * @internal
 */
final class EntityPropertiesTest extends TestCase {
	use SchemaRepositoryAware;

	/**
	 * @dataProvider LabelDataProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getPropertyLabel()
	 */
	public function testEntityPropertyLabels( string $property, string $label ) : void {
		$entity = new EntitySchema( 'Company', $this->getRegistry() );

		$actual_label = $entity->getPropertyLabel( $property );
		$this->assertEquals( $label, $actual_label );
	}

	public function LabelDataProvider() : array {
		return [
			['id', 'id'],
			['registrationNumber', 'Registration number'],
			'non-existent property' => ['foobar', 'foobar'],
		];
	}

	/**
	 * @dataProvider DescriptionsDataProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getPropertyDescription()
	 */
	public function testEntityPropertyDescriptions( string $property, ?string $descr ) : void {
		$entity = new EntitySchema( 'Company', $this->getRegistry() );

		$actual_descr = $entity->getPropertyDescription( $property );
		$this->assertEquals( $descr, $actual_descr );
	}

	public function DescriptionsDataProvider() : array {
		return [
			['voenCode', 'Azerbaijan taxpayer ID'],
			'property with no description' => ['registrationNumber', null],
			'non-existent property'        => ['foobar', null],
		];
	}

	/**
	 * @dataProvider TypesDataProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getPropertyType()
	 */
	public function testEntityPropertyTypes( string $property, ?PropertyType $type ) : void {
		$entity = new EntitySchema( 'Person', $this->getRegistry() );

		$actual_type = $entity->getPropertyType( $property );
		$this->assertEquals( $type, $actual_type );
	}

	public function TypesDataProvider() : array {
		return [
			['nationality', PropertyType::Country],
			['birthDate', PropertyType::Date],
			'no property type is set' => ['secondName', PropertyType::String],
			'non-existent property'   => ['foobar', null],
		];
	}

	/**
	 * @dataProvider SchemaNameDataProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getSchemaLabel()
	 * @covers \FollowTheMoney\EntitySchema::getSchemaName()
	 */
	public function testEntitySchemaName( string $entity, string $schema_name, string $schema_label ) : void {
		$entity = new EntitySchema( $entity, $this->getRegistry() );

		$this->assertSame( $schema_name, $entity->getSchemaName() );
		$this->assertSame( $schema_label, $entity->getSchemaLabel() );
	}

	public function SchemaNameDataProvider() : array {
		return [
			'company'      => ['Company', 'Company', 'Company'],
			'bank account' => ['BankAccount', 'BankAccount', 'Bank account'],
		];
	}
}
