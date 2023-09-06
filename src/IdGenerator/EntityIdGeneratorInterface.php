<?php
namespace FollowTheMoney\IdGenerator;

use FollowTheMoney\EntitySchema;

interface EntityIdGeneratorInterface {
	/**
	 * @param EntitySchema $entity
	 *
	 * @return string
	 */
	public function generate( EntitySchema $entity ) : string;
}
