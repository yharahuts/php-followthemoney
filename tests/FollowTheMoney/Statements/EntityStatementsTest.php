<?php
namespace Tests\FollowTheMoney\Statements;

use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\PropertyType;
use FollowTheMoney\Statements\EntityStatement;
use FollowTheMoney\Statements\EntityStatementFactory;
use PHPUnit\Framework\TestCase;
use Tests\Support\ReadableStatementIdGenerator;
use Tests\Support\SchemaRepositoryAware;
use Tests\Support\StatementFactoryAware;

/**
 * @internal
 */
class EntityStatementsTest extends TestCase {
	use SchemaRepositoryAware;
	use StatementFactoryAware;

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementFactory::fromJson
	 */
	public function testStatementIsCreatedFromJson() : void {
		$statement = $this->getStatementFactory()->fromJson( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}' );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statement->entity_id );
	}

	/**
	 * @covers \FollowTheMoney\IdGenerator\Sha1StatementIdGenerator
	 * @covers \FollowTheMoney\Statements\EntityStatementFactory::create
	 */
	public function testStatementIdIsGeneratedOnCreation() : void {
		$statement = $this->getStatementFactory()->create( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', 'PublicBody', 'country', 'ua' );

		$this->assertSame( 'a01c4157b3c785ba64fba019ba5a97632457fde2', $statement->id );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatement
	 */
	public function testStatementIsImmutable() : void {
		$statement = new EntityStatement( id: 'foobar', entity_id: 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', schema: 'PublicBody', prop: 'country', prop_type: PropertyType::Country, val: 'ua' );

		$this->expectException( \Error::class );
		$this->expectExceptionMessage( 'Cannot modify readonly property' );

		/** @psalm-suppress InaccessibleProperty */
		$statement->val = 'en';
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementFactory::fromJson
	 *
	 * @dataProvider StatementsProvider
	 */
	public function testStatementMustHaveItsIdGenerated( string $statement_json, string $expected_id ) : void {
		$statement = $this->getStatementFactory()->fromJson( $statement_json );
		$actual_id = $statement->id;

		$this->assertSame( $expected_id, $actual_id );
	}

	public function StatementsProvider() : \Generator {
		yield 'json with statement id' => [
			'json'        => '{"id":"foobar","entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}',
			'expected_id' => 'foobar',
		];

		yield 'json without statement id' => [
			'json'        => '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}',
			'expected_id' => 'a01c4157b3c785ba64fba019ba5a97632457fde2',
		];

		yield 'json with empty statement id' => [
			'json'        => '{"id":"","entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}',
			'expected_id' => 'a01c4157b3c785ba64fba019ba5a97632457fde2',
		];
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementFactory::fromJson
	 *
	 * @dataProvider PropertyTypesProvider
	 */
	public function testStatementPropertyTypeIsTakenFromSchema( string $statement_json, PropertyType $expected_type ) : void {
		$statement = $this->getStatementFactory()->fromJson( $statement_json );

		$this->assertSame( $expected_type, $statement->prop_type );
	}

	public function PropertyTypesProvider() : \Generator {
		yield 'property with type' => [
			'json'          => '{"entity_id":"foo","schema":"PublicBody","prop":"country","val":"ua"}',
			'expected_type' => PropertyType::Country,
		];

		yield 'property inherited from parent schema' => [
			'json'          => '{"entity_id":"foo","schema":"PublicBody","prop":"addressEntity","val":"bar"}',
			'expected_type' => PropertyType::Entity,
		];

		yield 'property without type in schema' => [
			'json'          => '{"entity_id":"foo","schema":"Person","prop":"firstName","val":"Ivan"}',
			'expected_type' => PropertyType::String,
		];

		yield 'json with statement id' => [
			'json'          => '{"id":"foobar","entity_id":"foo","schema":"PublicBody","prop":"country","val":"ua"}',
			'expected_type' => PropertyType::Country,
		];

		yield 'json with wrong property type' => [
			'json'          => '{"entity_id":"foo","schema":"PublicBody","prop":"country","prop_type":"date","val":"ua"}',
			'expected_type' => PropertyType::Country,
		];

		yield 'id statement' => [
			'json'          => '{"entity_id":"foo","schema":"Person","prop":"id","val":"foo"}',
			'expected_type' => PropertyType::Id,
		];
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementFactory::fromArray
	 *
	 * @dataProvider CustomGeneratorProvider
	 */
	public function testStatementIdIsGeneratedWithCustomGenerator( array $statement, string $expected_id ) : void {
		$factory = new EntityStatementFactory( $this->getRegistry(), new ReadableStatementIdGenerator() );

		$this->assertSame( $expected_id, $factory->fromArray( $statement )->id );
	}

	public function CustomGeneratorProvider() : \Generator {
		yield 'array without statement id' => [
			'statement'   => ['entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'name', 'val' => 'Ivan'],
			'expected_id' => 'Person:foo:name:Ivan',
		];

		yield 'array with statement id' => [
			'statement'   => ['id' => 'foobar', 'entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'name', 'val' => 'Ivan'],
			'expected_id' => 'foobar',
		];
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementFactory::fromJson
	 *
	 * @dataProvider BrokenJsonProvider
	 */
	public function testCreatingAStatementFromBrokenJsonThrowsException( string $json ) : void {
		$this->expectException( StatementException::class );
		$this->getStatementFactory()->fromJson( $json );
	}

	public function BrokenJsonProvider() : \Generator {
		yield 'invalid json' => [
			'json' => 'foobar',
		];

		yield 'invalid entity schema' => [
			'json' => '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody"}',
		];

		yield 'unknown property' => [
			'json' => '{"entity_id":"foo","schema":"PublicBody","prop":"foobar","val":"ua"}',
		];

		yield 'unknown schema' => [
			'json' => '{"entity_id":"foo","schema":"FooBar","prop":"name","val":"ua"}',
		];

		yield 'id statement of unknown schema' => [
			'json' => '{"entity_id":"foo","schema":"FooBar","prop":"id","val":"foo"}',
		];
	}
}
