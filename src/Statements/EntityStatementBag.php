<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\Exceptions\StatementException;

/**
 * @psalm-import-type StatementArray from EntityStatement
 *
 * @psalm-consistent-constructor
 *
 * @template-implements \IteratorAggregate<int,EntityStatement>
 * @template-implements \ArrayAccess<int,EntityStatement>
 */
class EntityStatementBag implements \IteratorAggregate, \Countable, \ArrayAccess, \JsonSerializable {
	/** @var array<int, EntityStatement> */
	protected array $list = [];

	public function append( EntityStatement $statement ) : static {
		$this->list[ ] = $statement;

		return $this;
	}

	/**
	 * @return \ArrayIterator<int, EntityStatement>
	 */
	public function getIterator() : \ArrayIterator {
		return new \ArrayIterator( $this->list );
	}

	/** {@inheritDoc} */
	public function offsetExists( mixed $offset ) : bool {
		return array_key_exists( $offset, $this->list );
	}

	/** {@inheritDoc} */
	public function offsetGet( mixed $offset ) : mixed {
		return $this->list[ $offset ] ?? null;
	}

	/** {@inheritDoc} */
	public function offsetSet( mixed $offset, mixed $value ) : void {
		if ( is_null( $offset ) ) {
			$this->list[ ] = $value;
		} else {
			$this->list[ $offset ] = $value;
		}
	}

	/** {@inheritDoc} */
	public function offsetUnset( mixed $offset ) : void {
		unset( $this->list[ $offset ] );
	}

	/** {@inheritDoc} */
	public function count() : int {
		return count( $this->list );
	}

	/**
	 * @return array<int, StatementArray>
	 */
	public function toArray() : array {
		return array_map( fn ( EntityStatement $item ) => $item->toArray(), $this->list );
	}

	/**
	 * Return statements as JSON Lines.
	 */
	public function toJson( int $flags = JSON_UNESCAPED_UNICODE ) : string {
		$statements = array_map( fn ( EntityStatement $item ) => $item->toJson( $flags ), $this->list );

		return join( "\n", $statements );
	}

	/** {@inheritDoc} */
	public function jsonSerialize() : array {
		return $this->toArray();
	}

	/**
	 * @throws StatementException
	 */
	public static function fromJson( string $lines, EntityStatementFactory $factory ) : static {
		$lines = explode( "\n", $lines );
		$bag = new static();

		foreach ( $lines as $json ) {
			$statement = $factory->fromJson( $json );
			$bag->append( $statement );
		}

		return $bag;
	}

	/**
	 * @throws StatementException
	 */
	public static function fromArray( array $array, EntityStatementFactory $factory ) : static {
		$bag = new static();

		try {
			foreach ( $array as $item ) {
				if ( !is_array( $item ) ) {
					throw new StatementException( 'Expected statement array, got '.get_debug_type( $item ) );
				}

				$statement = $factory->fromArray( $item );
				$bag->append( $statement );
			}
		} catch ( \Throwable $e ) {
			throw new StatementException( 'Failed to init statements: '.$e->getMessage(), 0, $e );
		}

		return $bag;
	}
}
