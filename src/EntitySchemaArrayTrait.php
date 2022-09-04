<?php
	namespace FollowTheMoney;

	trait EntitySchemaArrayTrait {
		/** @inheritDoc */
		public function getIterator( ) {
			return new \ArrayIterator( $this->values );
		}

		/** @inheritDoc */
		public function count( ) {
			return count( $this->values );
		}

		/** @inheritDoc */
		public function offsetExists( $offset ) {
			return isset( $this->values[ $offset ] );
		}

		/** @inheritDoc */
		public function offsetGet( $offset ) {
			return $this->get( $offset );
		}

		/** @inheritDoc */
		public function offsetSet( $offset, $value ) {
			$this->set( $offset, $value );
		}

		/** @inheritDoc */
		public function offsetUnset( $offset ) {
			unset( $this->values[ $offset ] );
		}
	}