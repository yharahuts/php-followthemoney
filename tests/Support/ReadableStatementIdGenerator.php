<?php
namespace Tests\Support;

use FollowTheMoney\IdGenerator\StatementIdGenerator;

class ReadableStatementIdGenerator implements StatementIdGenerator {
	public function generate( string $entity_id, string $schema, string $prop, string $val ) : string {
		return "{$schema}:{$entity_id}:{$prop}:{$val}";
	}
}
