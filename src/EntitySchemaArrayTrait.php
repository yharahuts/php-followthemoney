<?php
namespace FollowTheMoney;

trait EntitySchemaArrayTrait {
	/** {@inheritDoc} */
	public function getIterator() : \ArrayIterator {
		return new \ArrayIterator( $this->values );
	}

	/** {@inheritDoc} */
	public function count() : int {
		return count( $this->values );
	}

	/** {@inheritDoc} */
	public function offsetExists( mixed $offset ) : bool {
		return isset( $this->values[ $offset ] );
	}

	/** {@inheritDoc} */
	public function offsetGet( mixed $offset ) : ?array {
		return $this->get( $offset );
	}

	/** {@inheritDoc} */
	public function offsetSet( mixed $offset, mixed $value ) : void {
		$this->set( $offset, $value );
	}

	/** {@inheritDoc} */
	public function offsetUnset( mixed $offset ) : void {
		unset( $this->values[ $offset ] );
	}
}
