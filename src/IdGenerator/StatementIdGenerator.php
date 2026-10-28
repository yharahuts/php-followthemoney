<?php
namespace FollowTheMoney\IdGenerator;

interface StatementIdGenerator {
	public function generate( string $entity_id, string $schema, string $prop, string $val ) : string;
}
