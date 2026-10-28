<?php
namespace FollowTheMoney\IdGenerator;

use FollowTheMoney\EntitySchema;

interface EntityIdGenerator {
	public function generate( EntitySchema $entity ) : string;
}
