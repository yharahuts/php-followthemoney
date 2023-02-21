<?php
namespace FollowTheMoney\Schema;

use FollowTheMoney\Schema\Exceptions\FtmSchemaException;

class YamlSchemaRegistry implements SchemaRegistryInterface {
	/** @var string[] */
	public const YAML_FILE_EXT = [ 'yml', 'yaml' ];
	protected string $dir;
	protected array $schemas = [ ];

	/**
	 * YamlSchemaRegistry constructor.
	 *
	 * @param string $dir
	 */
	public function __construct( string $dir ) {
		if ( !str_ends_with( $dir, '/' ) ) {
			$dir .= '/';
		}

		$this->dir = $dir;

		$this->processSchemasInDirectory();
	}

	/** {@inheritdoc} */
	public function has( string $schema ) : bool {
		return array_key_exists( $schema, $this->schemas );
	}

	/** {@inheritdoc} */
	public function get( string $schema ) : array {
		if ( !$this->has( $schema ) ) {
			throw new FtmSchemaException( "No such schema defined: {$schema}" );
		}

		return $this->schemas[ $schema ];
	}

	private function processSchemasInDirectory() {
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

	/**
	 * @param string $file
	 *
	 * @return bool
	 */
	private function needsProcessing( string $file ) : bool {
		$ext = pathinfo( $file, PATHINFO_EXTENSION );
		$ext = strtolower( $ext );

		return in_array( $ext, static::YAML_FILE_EXT );
	}

	/**
	 * @param string $file
	 */
	private function processSchemaDefinition( string $file ) : void {
		$path = $this->dir.$file;
		$schema_name = pathinfo( $file, PATHINFO_FILENAME );

		$yaml = yaml_parse_file( $path );

		if ( ( $yaml === false ) || empty( $yaml[ $schema_name ] ) ) {
			throw new FtmSchemaException( "Failed to parse schema for {$schema_name} in {$path}" );
		}

		$schema_def = $yaml[ $schema_name ];
		$this->schemas[ $schema_name ] = $schema_def;
	}
}
