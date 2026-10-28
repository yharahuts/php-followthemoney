<?php
namespace Tests\FollowTheMoney\Statements;

use FollowTheMoney\Exceptions\StatementException;
use FollowTheMoney\PropertyType;
use FollowTheMoney\Statements\EntityStatement;
use FollowTheMoney\Statements\EntityStatementBag;
use PHPUnit\Framework\TestCase;
use Tests\Support\SchemaRepositoryAware;
use Tests\Support\StatementFactoryAware;

/**
 * @internal
 */
class EntityStatementBagTest extends TestCase {
	use SchemaRepositoryAware;
	use StatementFactoryAware;

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::fromJson
	 */
	public function testFromJson() : void {
		$lines = <<<'JSON'
{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}
{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"taxNumber","val":"20064120"}
JSON;

		$statements = EntityStatementBag::fromJson( $lines, $this->getStatementFactory() );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statements[ 0 ]->entity_id );
		$this->assertEquals( 'ua', $statements[ 0 ]->val );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::fromJson
	 */
	public function testFromBrokenJson() : void {
		$this->expectException( StatementException::class );
		$statements = EntityStatementBag::fromJson( 'foo bar', $this->getStatementFactory() );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::fromArray
	 */
	public function testFromArray() : void {
		$array = [
			json_decode( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}', true ),
			json_decode( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"taxNumber","val":"20064120"}', true ),
		];

		$statements = EntityStatementBag::fromArray( $array, $this->getStatementFactory() );
		$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statements[ 0 ]->entity_id );
		$this->assertEquals( 'ua', $statements[ 0 ]->val );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::fromArray
	 */
	public function testFromBrokenArray() : void {
		$this->expectException( StatementException::class );
		$statements = EntityStatementBag::fromArray( ['foo'], $this->getStatementFactory() );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::offsetSet
	 */
	public function testAppendWithArraySyntax() : void {
		$first = new EntityStatement( id: 'first', entity_id: 'foo', schema: 'Person', prop: 'name', prop_type: PropertyType::Name, val: 'first' );
		$second = new EntityStatement( id: 'second', entity_id: 'foo', schema: 'Person', prop: 'name', prop_type: PropertyType::Name, val: 'second' );

		$bag = new EntityStatementBag();
		$bag[ ] = $first;
		$bag[ ] = $second;

		$this->assertCount( 2, $bag );
		$this->assertSame( $first, $bag[ 0 ] );
		$this->assertSame( $second, $bag[ 1 ] );
	}

	/**
	 * @covers \FollowTheMoney\Statements\EntityStatementBag::offsetSet
	 */
	public function testSetWithExplicitOffset() : void {
		$first = new EntityStatement( id: 'first', entity_id: 'foo', schema: 'Person', prop: 'name', prop_type: PropertyType::Name, val: 'first' );
		$second = new EntityStatement( id: 'second', entity_id: 'foo', schema: 'Person', prop: 'name', prop_type: PropertyType::Name, val: 'second' );

		$bag = new EntityStatementBag();
		$bag[ ] = $first;
		$bag[ 0 ] = $second;

		$this->assertCount( 1, $bag );
		$this->assertSame( $second, $bag[ 0 ] );
	}
}
