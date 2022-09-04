<?php

	namespace FollowTheMoney\Statements;

	use FollowTheMoney\Exceptions\StatementException;
	use PHPUnit\Framework\TestCase;

	class EntityStatementTest extends TestCase {
		/**
		 * @covers \FollowTheMoney\Statements\EntityStatement::fromJson
		 */
		public function test_from_json( ) {
			$statement = EntityStatement::fromJson( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody","prop":"country","val":"ua"}' );
			$this->assertEquals( $statement->entity_id, 'bea008dac1ea309d22e100ceb0a5f3a44db882fa' );
		}

		/**
		 * @covers \FollowTheMoney\Statements\EntityStatement::fromJson
		 */
		public function test_from_broken_json( ) {
			$this->expectException( StatementException::class );
			$statement = EntityStatement::fromJson( 'foobar' );
		}

		/**
		 * @covers \FollowTheMoney\Statements\EntityStatement::fromJson
		 */
		public function test_from_broken_json_2( ) {
			$this->expectException( StatementException::class );
			$statement = EntityStatement::fromJson( '{"entity_id":"bea008dac1ea309d22e100ceb0a5f3a44db882fa","schema":"PublicBody"}' );
		}
	}
