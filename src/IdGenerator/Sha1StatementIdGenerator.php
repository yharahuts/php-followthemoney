<?php
namespace FollowTheMoney\IdGenerator;

class Sha1StatementIdGenerator implements StatementIdGenerator {
	/** {@inheritDoc} */
	public function generate( string $entity_id, string $schema, string $prop, string $val ) : string {
		return sha1( "entity.{$entity_id}.{$prop}.{$val}" );
	}
}
