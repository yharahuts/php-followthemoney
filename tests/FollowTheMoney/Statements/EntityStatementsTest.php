<?php
namespace Tests\FollowTheMoney\Statements;

use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Statements\EntityStatement;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class EntityStatementsTest extends TestCase {
	/**
	 * @covers \FollowTheMoney\Statements\EntityStatement::fromJson
	 */
	public function testStatementIsCreatedFromJson() {
		$statement = EntityStatement::fromJson( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}' );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statement->getEntityId() );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatement
	 *
	 * @dataProvider StatementsProvider
	 */
	public function testStatementMustHaveItsIdGenerated( string $statement_json, string $expected_id ) {
		$statement = EntityStatement::fromJson( $statement_json );
		$actual_id = $statement->getId();

		$this->assertSame( $expected_id, $actual_id );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatement::fromJson
	 *
	 * @dataProvider BrokenJsonProvider
	 */
	public function testCreatingAStatementFromBrokenJsonThrowsException( string $json ) {
		$this->expectException( StatementException::class );
		EntityStatement::fromJson( $json );
	}

	public function BrokenJsonProvider() : \Generator {
		yield 'invalid json' => [
			'json' => 'foobar',
		];

		yield 'invalid entity schema' => [
			'json' => '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody"}',
		];
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
}
