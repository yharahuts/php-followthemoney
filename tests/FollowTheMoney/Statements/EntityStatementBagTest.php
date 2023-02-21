<?php
namespace Tests\FollowTheMoney\Statements;

use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\Statements\EntityStatementBag;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class EntityStatementBagTest extends TestCase {
	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::fromJson
	 */
	public function testFromJson() {
		$lines = <<<'JSON'
{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}
{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"taxNumber","val":"20064120"}
JSON;

		$statements = EntityStatementBag::fromJson( $lines );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statements[ 0 ]->getEntityId() );
		$this->assertEquals( 'ua', $statements[ 0 ]->getValue() );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::fromJson
	 */
	public function testFromBrokenJson() {
		$this->expectException( StatementException::class );
		$statements = EntityStatementBag::fromJson( 'foo bar' );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::fromJson
	 */
	public function testFromArray() {
		$array = [
			json_decode( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}', true ),
			json_decode( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"taxNumber","val":"20064120"}', true ),
		];

		$statements = EntityStatementBag::fromArray( $array );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statements[ 0 ]->getEntityId() );
		$this->assertEquals( 'ua', $statements[ 0 ]->getValue() );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::fromJson
	 */
	public function testFromBrokenArray() {
		$this->expectException( StatementException::class );
		$statements = EntityStatementBag::fromArray( [ 'foo' ] );
	}
}
