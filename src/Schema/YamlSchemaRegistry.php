<?php
namespace FollowTheMoney\Schema;

use FollowTheMoney\Schema\Exceptions\FtmSchemaException;

/**
 * @psalm-import-type SchemaDefinition from SchemaRegistry
 */
final class YamlSchemaRegistry implements SchemaRegistry {
	/** @var string[] */
	public const array YAML_FILE_EXT = ['yml', 'yaml'];
	private string $dir;

	/** @var array<string, SchemaDefinition> */
	private array $schemas = [];

	public function __construct( string $dir ) {
		if ( !str_ends_with( $dir, '/' ) ) {
			$dir .= '/';
		}

		$this->dir = $dir;

		$this->processSchemasInDirectory();
	}

	/** {@inheritDoc} */
	public function has( string $schema ) : bool {
		return array_key_exists( $schema, $this->schemas );
	}

	/** {@inheritDoc} */
	public function get( string $schema ) : array {
		if ( !$this->has( $schema ) ) {
			throw new FtmSchemaException( "No such schema defined: {$schema}" );
		}

		return $this->schemas[ $schema ];
	}

	private function processSchemasInDirectory() : void {
		if ( !file_exists( $this->dir ) ) {
			throw new FtmSchemaException( "No such directory exists: {$this->dir}" );
		}

		$files = scandir( $this->dir );

		foreach ( $files as $file ) {
			if ( !$this->needsProcessing( $file ) ) {
				continue;
			}

			$this->processSchemaDefinition( $file );
		}
	}

	private function needsProcessing( string $file ) : bool {
		$ext = pathinfo( $file, PATHINFO_EXTENSION );
		$ext = strtolower( $ext );

		return in_array( $ext, self::YAML_FILE_EXT );
	}

	private function processSchemaDefinition( string $file ) : void {
		$path = $this->dir.$file;
		$schema_name = pathinfo( $file, PATHINFO_FILENAME );

		$yaml = yaml_parse_file( $path );

		if ( !is_array( $yaml ) || empty( $yaml[ $schema_name ] ) || !is_array( $yaml[ $schema_name ] ) ) {
			throw new FtmSchemaException( "Failed to parse schema for {$schema_name} in {$path}" );
		}

		/** @var SchemaDefinition $schema_def */
		$schema_def = $yaml[ $schema_name ];
		$this->schemas[ $schema_name ] = $schema_def;
	}
}
