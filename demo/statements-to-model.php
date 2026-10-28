<?php

use FollowTheMoney\IdGenerator\Sha1StatementIdGenerator;
use FollowTheMoney\Schema\YamlSchemaRegistry;
use FollowTheMoney\Statements\EntityStatementBag;
use FollowTheMoney\Statements\EntityStatementFactory;
use FollowTheMoney\Statements\EntityStatementsGenerator;

require 'vendor/autoload.php';

$schemaRegistry = new YamlSchemaRegistry( 'followthemoney/followthemoney/schema/' );
$factory = new EntityStatementFactory( $schemaRegistry, new Sha1StatementIdGenerator() );
$generator = new EntityStatementsGenerator( $schemaRegistry, $factory );
$bag = new EntityStatementBag();

while ( true ) {
	$line = trim( fgets( STDIN ) );
	if ( !$line ) {
		break;
	}

	$statement = $factory->fromJson( $line );
	$bag->append( $statement );
}

echo $generator->pack( $bag )->toJson();
