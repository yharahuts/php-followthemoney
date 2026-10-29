<?php
namespace FollowTheMoney;

trait EntitySchemaArrayTrait {
	/**
	 * @return \ArrayIterator<string, string[]>
	 */
	public function getIterator() : \ArrayIterator {
		return new \ArrayIterator( $this->values );
	}

	/** {@inheritDoc} */
	public function count() : int {
		return count( $this->values );
	}

	/**
	 * @param string $offset
	 */
	public function offsetExists( mixed $offset ) : bool {
		return isset( $this->values[ $offset ] );
	}

	/**
	 * @param string $offset
	 *
	 * @return string[]|null
	 */
	public function offsetGet( mixed $offset ) : ?array {
		return $this->get( $offset );
	}

	/**
	 * @param string $offset
	 * @param string|string[] $value
	 */
	public function offsetSet( mixed $offset, mixed $value ) : void {
		$this->set( $offset, $value );
	}

	/**
	 * @param string $offset
	 */
	public function offsetUnset( mixed $offset ) : void {
		unset( $this->values[ $offset ] );
	}
}
