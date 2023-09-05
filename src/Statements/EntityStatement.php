<?php
namespace FollowTheMoney\Statements;

use FollowTheMoney\Exceptions\StatementException;

/**
 * Class EntityStatement.
 */
class EntityStatement implements \JsonSerializable {
	/** @var string */
	protected string $statement_id;

	/** @var string */
	protected string $entity_id;

	/** @var string */
	protected string $schema;

	/** @var string */
	protected string $prop;

	/** @var string */
	protected string $val;

	/**
	 * @param string $entity_id
	 *
	 * @return $this
	 */
	public function setEntityId( string $entity_id ) : static {
		$this->entity_id = $entity_id;

		return $this;
	}

	/**
	 * @param string $schema
	 *
	 * @return $this
	 */
	public function setSchema( string $schema ) : static {
		$this->schema = $schema;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSchema() : string {
		return $this->schema;
	}

	/**
	 * @param $prop
	 *
	 * @return $this
	 */
	public function setProp( $prop ) : static {
		$this->prop = $prop;

		return $this;
	}

	/**
	 * @param $val
	 *
	 * @return $this
	 */
	public function setValue( $val ) : static {
		$this->val = $val;

		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray() : array {
		if ( !isset( $this->statement_id ) ) {
			// this smells bad
			$this->regenerateStatementId();
		}

		return [
			'id'        => $this->statement_id,
			'entity_id' => $this->entity_id,
			'schema'    => $this->schema,
			'prop'      => $this->prop,
			'val'       => $this->val,
		];
	}

	/**
	 * @param int $flags
	 *
	 * @return string
	 */
	public function toJson( $flags = JSON_UNESCAPED_UNICODE ) : string {
		return json_encode( $this->toArray(), $flags );
	}

	/** {@inheritDoc} */
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * @param string $json
	 *
	 * @return EntityStatement
	 *
	 * @throws StatementException
	 */
	public static function fromJson( string $json ) : EntityStatement {
		$array = json_decode( $json, true );

		if ( json_last_error() !== 0 ) {
			throw new StatementException( 'Failed to parse json: '.json_last_error_msg() );
		}

		return static::fromArray( $array );
	}

	/**
	 * @param array $array
	 *
	 * @return EntityStatement
	 *
	 * @throws StatementException
	 */
	public static function fromArray( array $array ) : EntityStatement {
		$item = new static();

		try {
			$item
				->setEntityId( $array[ 'entity_id' ] )
				->setValue( $array[ 'val' ] )
				->setProp( $array[ 'prop' ] )
				->setSchema( $array[ 'schema' ] )
			;

			if ( !empty( $array[ 'id' ] ) ) {
				$item->setId( $array[ 'id' ] );
			} else {
				$item->regenerateStatementId();
			}
		} catch ( \Throwable $e ) {
			throw new StatementException( 'Failed to init statement: '.$e->getMessage(), 0, $e );
		}

		return $item;
	}

	/**
	 * @return string
	 */
	public function getEntityId() : string {
		return $this->entity_id;
	}

	/**
	 * @return string
	 */
	public function getPropertyName() : string {
		return $this->prop;
	}

	/**
	 * @return string
	 */
	public function getValue() : string {
		return $this->val;
	}

	/**
	 * @return string
	 */
	public function getId() : string {
		return $this->statement_id;
	}

	/**
	 * @param string $statement_id
	 *
	 * @return $this
	 */
	public function setId( string $statement_id ) : static {
		$this->statement_id = $statement_id;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function regenerateStatementId() : static {
		// todo: allow custom generators

		$key = "entity.{$this->entity_id}.{$this->prop}.{$this->val}";
		$this->statement_id = sha1( $key );

		return $this;
	}
}
