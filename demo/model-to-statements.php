<?php

use FollowTheMoney\EntitySchema;
use FollowTheMoney\IdGenerator\Sha1StatementIdGenerator;
use FollowTheMoney\Schema\YamlSchemaRegistry;
use FollowTheMoney\Statements\EntityStatementFactory;
use FollowTheMoney\Statements\EntityStatementsGenerator;

require 'vendor/autoload.php';

$schemaRegistry = new YamlSchemaRegistry( 'followthemoney/followthemoney/schema/' );
$factory = new EntityStatementFactory( $schemaRegistry, new Sha1StatementIdGenerator() );
$generator = new EntityStatementsGenerator( $schemaRegistry, $factory );

while ( true ) {
	$line = trim( fgets( STDIN ) );
	if ( !$line ) {
		break;
	}

	$entity = EntitySchema::fromJson( $line, $schemaRegistry );
	$statements = $generator->unpack( $entity );
	echo $statements->toJson(), "\n";
}
