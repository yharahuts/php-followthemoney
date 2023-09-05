<?php
namespace Tests\FollowTheMoney\Statements;

use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Statements\EntityStatement;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class EntityStatementTest extends TestCase {
	/**
	 * @covers \FollowTheMoney\Statements\EntityStatement::fromJson
	 */
	public function testStatementIsCreatedFromJson() {
		$statement = EntityStatement::fromJson( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}' );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statement->getEntityId() );
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
}
