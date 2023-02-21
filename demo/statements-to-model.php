<?php

	require 'vendor/autoload.php';

	$generator = new \FollowTheMoney\Statements\EntityStatementsGenerator();
	$bag = new \FollowTheMoney\Statements\EntityStatementBag();

	while ( true ) {
		$line = trim( fgets( STDIN ) );
		if ( !$line ) {
			break;
		}

		$statement = \FollowTheMoney\Statements\EntityStatement::fromJson( $line );
		$bag->append( $statement );
	}

	echo $generator->pack( $bag, 'followthemoney/followthemoney/schema/' )->toJson();
