<?php

	require 'vendor/autoload.php';

	$generator = new \FollowTheMoney\Statements\EntityStatementsGenerator();

	while ( true ) {
		$line = trim( fgets( STDIN ) );
		if ( !$line ) {
			break;
		}

		$entity = \FollowTheMoney\EntitySchema::fromJson( $line, 'followthemoney/followthemoney/schema/' );
		$statements = $generator->unpack( $entity );
		echo $statements->toJson();
	}
