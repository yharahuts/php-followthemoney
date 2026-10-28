<?php
namespace Tests\Support;

use FollowTheMoney\IdGenerator\Sha1StatementIdGenerator;
use FollowTheMoney\Schema\YamlSchemaRegistry;
use FollowTheMoney\Statements\EntityStatementFactory;

trait StatementFactoryAware {
	abstract protected function getRegistry() : YamlSchemaRegistry;

	protected function getStatementFactory() : EntityStatementFactory {
		return new EntityStatementFactory( $this->getRegistry(), new Sha1StatementIdGenerator() );
	}
}
