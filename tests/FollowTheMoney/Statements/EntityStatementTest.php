<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\Exceptions\StatementException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class EntityStatementTest extends TestCase {
	/**
	 * @covers \FollowTheMoney\Statements\EntityStatement::fromJson
	 */
	public function testFromJson() {
		$statement = EntityStatement::fromJson( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}' );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statement->getEntityId() );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatement::fromJson
	 */
	public function testFromBrokenJson() {
		$this->expectException( StatementException::class );
		$statement = EntityStatement::fromJson( 'foobar' );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatement::fromJson
	 */
	public function testFromBrokenJson2() {
		$this->expectException( StatementException::class );
		$statement = EntityStatement::fromJson( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody"}' );
	}
}
