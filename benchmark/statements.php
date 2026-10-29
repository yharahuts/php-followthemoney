<?php

use FollowTheMoney\EntitySchema;
use FollowTheMoney\IdGenerator\Sha1StatementIdGenerator;
use FollowTheMoney\Schema\YamlSchemaRegistry;
use FollowTheMoney\Statements\EntityStatementFactory;
use FollowTheMoney\Statements\EntityStatementsGenerator;

require __DIR__.'/../vendor/autoload.php';

// Measures per entity: EntitySchema::fromArray(), unpack (entity to statements) and pack (statements to entity).
// Entities are read from a FtM JSON Lines file, json decoding is not measured.
// Entities that fail on any step are skipped and listed at the end.

if ( !isset( $argv[ 1 ] ) ) {
	fwrite( STDERR, "Usage: php {$argv[ 0 ]} <entities.ftm.json> [schema dir]\n" );

	exit( 1 );
}

$file = fopen( $argv[ 1 ], 'r' );

if ( $file === false ) {
	exit( 1 );
}

$registry = new YamlSchemaRegistry( $argv[ 2 ] ?? __DIR__.'/../followthemoney/followthemoney/schema/' );
$generator = new EntityStatementsGenerator( $registry, new EntityStatementFactory( $registry, new Sha1StatementIdGenerator() ) );

$timings = array_fill_keys( ['fromArray', 'unpack', 'pack'], ['total' => 0, 'min' => PHP_INT_MAX, 'max' => 0] );
$entities = 0;
$statements = 0;
$skipped = 0;

/** @var array<string, int> $errors */
$errors = [];

$size = (int) filesize( $argv[ 1 ] );
$started = hrtime( true );
$reported = $started;

// Progress line on stderr, the percent and eta are by bytes read
$progress = function () use ( $file, $size, $started, &$entities, &$skipped ) : string {
	$elapsed = ( hrtime( true ) - $started ) / 1e9;
	$read = (int) ftell( $file );
	$line = sprintf( "\r%9d entities, %6d skipped, %7d/s, %s", $entities, $skipped, $entities / max( $elapsed, 1e-9 ), gmdate( 'H:i:s', (int) $elapsed ) );

	if ( $size > 0 && $read > 0 ) {
		$line = sprintf( "\r%5.1f%%", $read / $size * 100 ).$line.' eta '.gmdate( 'H:i:s', (int) ( $elapsed * ( $size - $read ) / $read ) );
	}

	return $line;
};

// Ctrl+C stops after the current entity and prints the results so far.
// The handler is also needed for php running as pid 1 in docker, which ignores SIGINT otherwise.
$interrupted = false;

if ( function_exists( 'pcntl_async_signals' ) ) {
	pcntl_async_signals( true );
	pcntl_signal( SIGINT, function () use ( &$interrupted ) : void {
		$interrupted = true;
	} );
}

while ( !$interrupted && ( $line = fgets( $file ) ) !== false ) {
	if ( hrtime( true ) - $reported > 5e8 ) {
		$reported = hrtime( true );
		fwrite( STDERR, $progress() );
	}

	$line = trim( $line );

	if ( $line === '' ) {
		continue;
	}

	try {
		$array = json_decode( $line, true, 512, JSON_THROW_ON_ERROR );

		$start = hrtime( true );
		$entity = EntitySchema::fromArray( $array, $registry );
		$from_array = hrtime( true ) - $start;

		$start = hrtime( true );
		$bag = $generator->unpack( $entity );
		$unpack = hrtime( true ) - $start;

		$start = hrtime( true );
		$generator->pack( $bag );
		$pack = hrtime( true ) - $start;
	} catch ( Throwable $e ) {
		++$skipped;
		$errors[ $e->getMessage() ] = ( $errors[ $e->getMessage() ] ?? 0 ) + 1;

		continue;
	}

	foreach ( ['fromArray' => $from_array, 'unpack' => $unpack, 'pack' => $pack] as $name => $ns ) {
		$timings[ $name ][ 'total' ] += $ns;
		$timings[ $name ][ 'min' ] = min( $timings[ $name ][ 'min' ], $ns );
		$timings[ $name ][ 'max' ] = max( $timings[ $name ][ 'max' ], $ns );
	}

	++$entities;
	$statements += count( $bag );
}

fwrite( STDERR, $progress()."\n" );

printf( "entities: %d, statements: %d, skipped: %d%s\n", $entities, $statements, $skipped, $interrupted ? ', interrupted' : '' );

if ( $entities > 0 ) {
	// Share of each step in the sum of all steps
	$sum = max( array_sum( array_column( $timings, 'total' ) ), 1 );

	printf( "\n%-10s %12s %12s %12s %12s %12s\n", '', 'total, s', 'share, %', 'avg, us', 'min, us', 'max, us' );

	foreach ( $timings as $name => $timing ) {
		printf(
			"%-10s %12.3f %12.1f %12.3f %12.3f %12.3f\n",
			$name,
			$timing[ 'total' ] / 1e9,
			$timing[ 'total' ] / $sum * 100,
			$timing[ 'total' ] / $entities / 1e3,
			$timing[ 'min' ] / 1e3,
			$timing[ 'max' ] / 1e3,
		);
	}
}

if ( $errors !== [] ) {
	arsort( $errors );
	echo "\nskipped, top errors:\n";

	foreach ( array_slice( $errors, 0, 10, true ) as $message => $count ) {
		printf( "%10d  %s\n", $count, $message );
	}
}

exit( $entities > 0 ? 0 : 1 );
