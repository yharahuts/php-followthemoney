<?php
	namespace FollowTheMoney\Tests;

	use FollowTheMoney\EntitySchema;
	use PHPUnit\Framework\TestCase;

	class EntityPropertiesTest extends TestCase {
		public function LabelDataProvider( ) {
			return [
				[ 'id', 'id' ],
				[ 'registrationNumber', 'Registration number' ],
				'non-existent property' => [ 'foobar', 'foobar' ],
			];
		}

		/**
		 * @dataProvider LabelDataProvider
		 * @covers EntitySchema::getPropertyLabel()
		 */
		public function testEntityPropertyLabels( string $property, string $label ) {
			$entity = new EntitySchema( 'Company', 'followthemoney/followthemoney/schema/' );

			$actual_label = $entity->getPropertyLabel( $property );
			$this->assertEquals( $label, $actual_label );
		}

		public function DescriptionsDataProvider( ) {
			return [
				[ 'voenCode', 'Azerbaijan taxpayer ID' ],
				'property with no description' => [ 'registrationNumber', null ],
				'non-existent property'        => [ 'foobar', null ],
			];
		}

		/**
		 * @dataProvider DescriptionsDataProvider
		 * @covers EntitySchema::getPropertyDescription()
		 */
		public function testEntityPropertyDescriptions( string $property, ?string $descr ) {
			$entity = new EntitySchema( 'Company', 'followthemoney/followthemoney/schema/' );

			$actual_descr = $entity->getPropertyDescription( $property );
			$this->assertEquals( $descr, $actual_descr );
		}

		public function TypesDataProvider( ) {
			return [
				[ 'nationality', 'country' ],
				[ 'birthDate', 'date' ],
				'no property type is set' => [ 'secondName', null ],
				'non-existent property'   => [ 'foobar', null ],
			];
		}

		/**
		 * @dataProvider TypesDataProvider
		 * @covers EntitySchema::getPropertyType()
		 */
		public function testEntityPropertyTypes( string $property, ?string $type ) {
			$entity = new EntitySchema( 'Person', 'followthemoney/followthemoney/schema/' );

			$actual_type = $entity->getPropertyType( $property );
			$this->assertEquals( $type, $actual_type );
		}

		public function SchemaNameDataProvider( ) {
			return [
				'company'      => [ 'Company', 'Company', 'Company' ],
				'bank account' => [ 'BankAccount', 'BankAccount', 'Bank account' ],
			];
		}

		/**
		 * @dataProvider SchemaNameDataProvider
		 * @covers EntitySchema::getSchemaName()
		 * @covers EntitySchema::getSchemaLabel()
		 */
		public function testEntitySchemaName( string $entity, string $schema_name, string $schema_label ) {
			$entity = new EntitySchema( $entity, 'followthemoney/followthemoney/schema/' );

			$this->assertSame( $schema_name, $entity->getSchemaName( ) );
			$this->assertSame( $schema_label, $entity->getSchemaLabel( ) );
		}
	}