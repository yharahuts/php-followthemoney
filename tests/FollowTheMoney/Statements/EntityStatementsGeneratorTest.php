<?php
namespace Tests\FollowTheMoney\Statements;

use FollowTheMoney\EntitySchema;
use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Exceptions\UnknownPropertyException;
use FollowTheMoney\Statements\EntityStatement;
use FollowTheMoney\Statements\EntityStatementBag;
use FollowTheMoney\Statements\EntityStatementFactory;
use FollowTheMoney\Statements\EntityStatementsGenerator;
use PHPUnit\Framework\TestCase;
use Tests\Support\ReadableStatementIdGenerator;
use Tests\Support\SchemaRepositoryAware;
use Tests\Support\StatementFactoryAware;

/**
 * @covers \FollowTheMoney\Statements\EntityStatement
 * @covers \FollowTheMoney\Statements\EntityStatementsGenerator
 *
 * @internal
 */
class EntityStatementsGeneratorTest extends TestCase {
	use SchemaRepositoryAware;
	use StatementFactoryAware;

	/** Generator options with id statement enabled, including the default ones. */
	private const array ID_STATEMENT_ON = [
		'default'         => [],
		'id statement on' => ['use_id_statement' => true],
	];
	private const array ID_STATEMENT_OFF = [
		'id statement off' => ['use_id_statement' => false],
	];

	public function testEntityIsUnpackedToStatements() : void {
		$json = '{"id": "bea008dac1ea309d22e100ceb0a5f3a44db882fa", "properties": {"addressEntity": ["e89d23f9af7daa0cc6d11a5701d23cbc9084a444"], "country": ["ua"], "incorporationDate": ["1990"], "jurisdiction": ["ua"], "mainCountry": ["ua"], "name": ["\u0412\u0420\u0423", "\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430 \u0420\u0430\u0434\u0430 \u0423\u043a\u0440\u0430\u0457\u043d\u0438"], "phone": ["+380442554246"], "taxNumber": ["20064120"], "website": ["https://www.rada.gov.ua/"], "wikipediaUrl": ["https://uk.wikipedia.org/wiki/\u0412\u0435\u0440\u0445\u043e\u0432\u043d\u0430_\u0420\u0430\u0434\u0430_\u0423\u043a\u0440\u0430\u0457\u043d\u0438"]}, "schema": "PublicBody"}';
		$registry = $this->getRegistry();
		$entity = EntitySchema::fromJson( $json, $registry );

		$generator = new EntityStatementsGenerator( $registry, $this->getStatementFactory() );
		$statements = $generator->unpack( $entity );

		$this->assertContainsOnlyInstancesOf( EntityStatement::class, $statements );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statements[ 0 ]->entity_id );
		$this->assertEquals( 'PublicBody', $statements[ 0 ]->schema );

		$this->assertEquals( $statements->toArray()[ 0 ][ 'schema' ], 'PublicBody' );
		$this->assertEquals( $statements->toArray()[ 0 ][ 'entity_id' ], 'bea008dac1ea309d22e100ceb0a5f3a44db882fa' );

		$statement = $statements[ 0 ]->toArray();
		$this->assertEquals( [
			'id'        => '806ae250f015700175126cfd2f1463b9ee6b96f1',
			'entity_id' => 'bea008dac1ea309d22e100ceb0a5f3a44db882fa',
			'schema'    => 'PublicBody',
			'prop'      => 'id',
			'prop_type' => 'id',
			'val'       => 'bea008dac1ea309d22e100ceb0a5f3a44db882fa',
		], $statement );

		$statement = $statements[ 1 ]->toArray();
		$this->assertEquals( [
			'id'        => 'b6355556a23cf37b316d495e4219c17ab33db1b9',
			'entity_id' => 'bea008dac1ea309d22e100ceb0a5f3a44db882fa',
			'schema'    => 'PublicBody',
			'prop'      => 'addressEntity',
			'prop_type' => 'entity',
			'val'       => 'e89d23f9af7daa0cc6d11a5701d23cbc9084a444',
		], $statement );

		$this->assertEquals( $statements[ 0 ]->toJson(), json_encode( $statements[ 0 ] ) );
		$this->assertEquals( json_decode( $statements[ 0 ]->toJson(), true ), $statements[ 0 ]->toArray() );
	}

	/**
	 * @dataProvider UnpackProvider
	 *
	 * @param array{use_id_statement?: bool} $options
	 */
	public function testEntityIsUnpackedWithCustomStatementIdGenerator( array $options, array $expected_ids ) : void {
		$registry = $this->getRegistry();
		$entity = new EntitySchema( 'Person', $registry );
		$entity->setId( 'foo' );
		$entity->set( 'firstName', 'Ivan' );
		$entity->set( 'birthDate', ['1975', '1975-01-02'] );

		$generator = new EntityStatementsGenerator( $registry, new EntityStatementFactory( $registry, new ReadableStatementIdGenerator() ), ...$options );
		$statements = $generator->unpack( $entity );

		$this->assertSame( $expected_ids, array_column( $statements->toArray(), 'id' ) );
	}

	public function UnpackProvider() : \Generator {
		foreach ( self::ID_STATEMENT_ON as $mode => $options ) {
			yield $mode => [
				'options'      => $options,
				'expected_ids' => ['Person:foo:id:foo', 'Person:foo:firstName:Ivan', 'Person:foo:birthDate:1975', 'Person:foo:birthDate:1975-01-02'],
			];
		}

		foreach ( self::ID_STATEMENT_OFF as $mode => $options ) {
			yield $mode => [
				'options'      => $options,
				'expected_ids' => ['Person:foo:firstName:Ivan', 'Person:foo:birthDate:1975', 'Person:foo:birthDate:1975-01-02'],
			];
		}
	}

	/**
	 * @dataProvider ModesProvider
	 *
	 * @param array{use_id_statement?: bool} $options
	 */
	public function testUnpackingUnknownPropertyThrowsException( array $options ) : void {
		$registry = $this->getRegistry();
		$entity = new EntitySchema( 'Person', $registry );
		$entity->setId( 'foo' );
		$entity->set( 'foobar', 'Ivan' );

		$generator = new EntityStatementsGenerator( $registry, $this->getStatementFactory(), ...$options );

		$this->expectException( UnknownPropertyException::class );
		$generator->unpack( $entity );
	}

	public function ModesProvider() : \Generator {
		foreach ( self::ID_STATEMENT_ON + self::ID_STATEMENT_OFF as $mode => $options ) {
			yield $mode => ['options' => $options];
		}
	}

	/**
	 * @dataProvider PackProvider
	 *
	 * @param array{use_id_statement?: bool} $options
	 */
	public function testStatementsArePackedToEntity( array $options, array $statements ) : void {
		$bag = EntityStatementBag::fromArray( $statements, $this->getStatementFactory() );
		$generator = new EntityStatementsGenerator( $this->getRegistry(), $this->getStatementFactory(), ...$options );

		$this->assertSame(
			['id' => 'foo', 'schema' => 'Person', 'properties' => ['firstName' => ['Ivan'], 'birthDate' => ['1975', '1975-01-02']]],
			$generator->pack( $bag )->toArray(),
		);
	}

	public function PackProvider() : \Generator {
		$id = ['entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'id', 'val' => 'foo'];
		$wrong_id = ['entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'id', 'val' => 'bar'];
		$first_name = ['entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'firstName', 'val' => 'Ivan'];
		$birth_dates = [
			['entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'birthDate', 'val' => '1975'],
			['entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'birthDate', 'val' => '1975-01-02'],
		];

		foreach ( self::ID_STATEMENT_ON + self::ID_STATEMENT_OFF as $mode => $options ) {
			yield "{$mode}: id statement first" => [
				'options'    => $options,
				'statements' => [$id, $first_name, ...$birth_dates],
			];

			yield "{$mode}: id statement in the middle" => [
				'options'    => $options,
				'statements' => [$first_name, $id, ...$birth_dates],
			];

			yield "{$mode}: no id statement" => [
				'options'    => $options,
				'statements' => [$first_name, ...$birth_dates],
			];
		}

		foreach ( self::ID_STATEMENT_OFF as $mode => $options ) {
			yield "{$mode}: id statement value differs from entity_id" => [
				'options'    => $options,
				'statements' => [$wrong_id, $first_name, ...$birth_dates],
			];

			yield "{$mode}: id statements with different values" => [
				'options'    => $options,
				'statements' => [$id, $wrong_id, $first_name, ...$birth_dates],
			];
		}
	}

	/**
	 * @dataProvider PackFailuresProvider
	 *
	 * @param array{use_id_statement?: bool} $options
	 */
	public function testPackThrowsException( array $options, array $statements, string $message ) : void {
		$bag = EntityStatementBag::fromArray( $statements, $this->getStatementFactory() );
		$generator = new EntityStatementsGenerator( $this->getRegistry(), $this->getStatementFactory(), ...$options );

		$this->expectException( StatementException::class );
		$this->expectExceptionMessage( $message );
		$generator->pack( $bag );
	}

	public function PackFailuresProvider() : \Generator {
		$id = ['entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'id', 'val' => 'foo'];
		$wrong_id = ['entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'id', 'val' => 'bar'];
		$first_name = ['entity_id' => 'foo', 'schema' => 'Person', 'prop' => 'firstName', 'val' => 'Ivan'];
		$foreign_last_name = ['entity_id' => 'bar', 'schema' => 'Person', 'prop' => 'lastName', 'val' => 'Sraka'];

		foreach ( self::ID_STATEMENT_ON + self::ID_STATEMENT_OFF as $mode => $options ) {
			yield "{$mode}: empty statements list" => [
				'options'    => $options,
				'statements' => [],
				'message'    => 'Empty statements list',
			];

			yield "{$mode}: statement of another entity" => [
				'options'    => $options,
				'statements' => [$id, $first_name, $foreign_last_name],
				'message'    => 'belongs to entity bar, expected foo',
			];

			yield "{$mode}: no id statement and statement of another entity" => [
				'options'    => $options,
				'statements' => [$first_name, $foreign_last_name],
				'message'    => 'belongs to entity bar, expected foo',
			];
		}

		foreach ( self::ID_STATEMENT_ON as $mode => $options ) {
			yield "{$mode}: id statement value differs from entity_id" => [
				'options'    => $options,
				'statements' => [$wrong_id, $first_name],
				'message'    => 'belongs to entity foo, expected bar',
			];

			yield "{$mode}: id statements with different values" => [
				'options'    => $options,
				'statements' => [$id, $wrong_id, $first_name],
				'message'    => 'has value bar, expected foo',
			];

			// id is taken from the id statement, so the first statement belongs to another entity
			yield "{$mode}: id statement after statement of another entity" => [
				'options'    => $options,
				'statements' => [$foreign_last_name, $id],
				'message'    => 'belongs to entity bar, expected foo',
			];
		}

		foreach ( self::ID_STATEMENT_OFF as $mode => $options ) {
			// id is taken from the first statement, so the id statement belongs to another entity
			yield "{$mode}: id statement after statement of another entity" => [
				'options'    => $options,
				'statements' => [$foreign_last_name, $id],
				'message'    => 'belongs to entity foo, expected bar',
			];
		}
	}

	/**
	 * @dataProvider RoundTripProvider
	 *
	 * @param array{use_id_statement?: bool} $unpack_options
	 * @param array{use_id_statement?: bool} $pack_options
	 */
	public function testUnpackedEntityIsPackedBack( array $unpack_options, array $pack_options ) : void {
		$registry = $this->getRegistry();
		$entity = new EntitySchema( 'Person', $registry );
		$entity->setId( 'foo' );
		$entity->set( 'firstName', 'Ivan' );
		$entity->set( 'birthDate', ['1975', '1975-01-02'] );

		$unpacker = new EntityStatementsGenerator( $registry, $this->getStatementFactory(), ...$unpack_options );
		$packer = new EntityStatementsGenerator( $registry, $this->getStatementFactory(), ...$pack_options );

		$this->assertSame( $entity->toArray(), $packer->pack( $unpacker->unpack( $entity ) )->toArray() );
	}

	public function RoundTripProvider() : \Generator {
		$modes = self::ID_STATEMENT_ON + self::ID_STATEMENT_OFF;

		foreach ( $modes as $unpack_mode => $unpack_options ) {
			foreach ( $modes as $pack_mode => $pack_options ) {
				yield "unpack {$unpack_mode}, pack {$pack_mode}" => [
					'unpack_options' => $unpack_options,
					'pack_options'   => $pack_options,
				];
			}
		}
	}

	public function testPackCreatesTopMostEntitySchema() : void {
		$this->markTestIncomplete();
	}

	public function testPackThrowsExceptionIfCantFigureOutTheTopmostSchema() : void {
		$this->markTestIncomplete();
	}
}
