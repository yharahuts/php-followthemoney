<?php

	namespace FollowTheMoney\Statements;

	use FollowTheMoney\Exceptions\StatementException;
	use PHPUnit\Framework\TestCase;

	class EntityStatementBagTest extends TestCase {
		/**
		 * @covers EntityStatementBag::fromJson
		 */
		public function test_from_json( ) {
			$lines = <<<JSON
{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}
{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"taxNumber","val":"20064120"}
JSON;

			$statements = EntityStatementBag::fromJson( $lines );
			$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statements[ 0 ]->entity_id );
			$this->assertEquals( 'ua', $statements[ 0 ]->val );
		}

		/**
		 * @covers EntityStatementBag::fromJson
		 */
		public function test_from_broken_json( ) {
			$this->expectException( StatementException::class );
			$statements = EntityStatementBag::fromJson( 'foo bar' );
		}

		/**
		 * @covers EntityStatementBag::fromArray
		 */
		public function test_from_array( ) {
			$array = [
				json_decode( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}', true ),
				json_decode( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"taxNumber","val":"20064120"}', true ),
			];

			$statements = EntityStatementBag::fromArray( $array );
			$this->assertEquals( 'bea008dac1ea309d22e100ceb0a5f3a44db882fa', $statements[ 0 ]->entity_id );
			$this->assertEquals( 'ua', $statements[ 0 ]->val );
		}

		/**
		 * @covers EntityStatementBag::fromArray
		 */
		public function test_from_broken_array( ) {
			$this->expectException( StatementException::class );
			$statements = EntityStatementBag::fromArray( [ 'foo' ] );
		}
	}
