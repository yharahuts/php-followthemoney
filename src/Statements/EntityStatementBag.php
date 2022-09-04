<?php
	namespace FollowTheMoney\Statements;

	use FollowTheMoney\Exceptions\StatementException;

	class EntityStatementBag implements \IteratorAggregate, \Countable, \ArrayAccess, \JsonSerializable {
		/** @var EntityStatement[] */
		protected $list = [ ];

		/**
		 * Add new statements to bag
		 * @return EntityStatement
		 */
		public function add( ) {
			$statement = new EntityStatement( );
			$this->list[ ] = $statement;
			return $statement;
		}

		/**
		 * @param EntityStatement $statement
		 * @return $this
		 */
		public function append( EntityStatement $statement ) {
			$this->list[ ] = $statement;
			return $this;
		}

		/** @inheritDoc */
		public function getIterator( ) {
			return new \ArrayIterator( $this->list );
		}

		/** @inheritDoc */
		public function offsetExists( $offset ) {
			return array_key_exists( $offset, $this->list );
		}

		/** @inheritDoc */
		public function offsetGet( $offset ) {
			return $this->list[ $offset ] ?? null;
		}

		/** @inheritDoc */
		public function offsetSet( $offset, $value ) {
			$this->list[ $offset ] = $value;
		}

		/** @inheritDoc */
		public function offsetUnset( $offset ) {
			unset( $this->list[ $offset ] );
		}

		/** @inheritDoc */
		public function count( ) {
			return count( $this->list );
		}

		/**
		 * @return array
		 */
		public function toArray( ) {
			return array_map( function ( $item ) {
				return $item->toArray( );
			}, $this->list );
		}

		/**
		 * Return statements as Json lines
		 * @param int $flags
		 * @return string
		 */
		public function toJson( $flags = JSON_UNESCAPED_UNICODE ) {
			$statements = array_map( function ( $item ) use ( $flags ) {
				return $item->toJson( $flags );
			}, $this->list );

			return join( "\n", $statements );
		}

		/** @inheritDoc */
		public function jsonSerialize( ) {
			return $this->toArray( );
		}

		/**
		 * @param string $lines
		 * @return static
		 * @throws \FollowTheMoney\Exceptions\StatementException
		 */
		public static function fromJson( string $lines ) {
			$lines = explode( "\n", $lines );
			$bag = new static( );

			foreach( $lines as $json ) {
				$statement = EntityStatement::fromJson( $json );
				$bag->append( $statement );
			}

			return $bag;
		}

		/**
		 * @param array $array
		 * @return static
		 * @throws \FollowTheMoney\Exceptions\StatementException
		 */
		public static function fromArray( array $array ) {
			$bag = new static( );

			try {
				foreach( $array as $item ) {
					$statement = EntityStatement::fromArray( $item );
					$bag->append( $statement );
				}
			} catch( \Throwable $e ) {
				throw new StatementException( "Failed to init statements: " . $e->getMessage( ), 0, $e );
			}

			return $bag;
		}
	}