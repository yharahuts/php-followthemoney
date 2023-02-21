<?php
namespace Tests\Support;

use FollowTheMoney\Schema\YamlSchemaRegistry;

trait SchemaRepositoryAware {
	protected function getRegistry() : YamlSchemaRegistry {
		return new YamlSchemaRegistry( 'followthemoney/followthemoney/schema/' );
	}
}
