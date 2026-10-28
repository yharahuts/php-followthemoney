<?php
namespace FollowTheMoney\Schema;

use FollowTheMoney\Schema\Exceptions\FtmSchemaException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FollowTheMoney\Schema\YamlSchemaRegistry
 *
 * @internal
 */
final class YamlSchemaRegistryTest extends TestCase {
	public function testThrowsExceptionIfNoDirectoryExists() : void {
		$this->expectException( FtmSchemaException::class );
		$registry = new YamlSchemaRegistry( 'foo bar' );
	}

	public function testThrowsExceptionIfFilenameGiven() : void {
		$this->expectException( FtmSchemaException::class );
		$registry = new YamlSchemaRegistry( 'followthemoney/followthemoney/schema/Address.yaml' );
	}

	public function testThrowsExceptionIfBrokenYaml() : void {
		$this->expectException( FtmSchemaException::class );
		$registry = new YamlSchemaRegistry( 'tests/data/schema' );
	}

	public function testHasSchema() : void {
		$registry = new YamlSchemaRegistry( 'followthemoney/followthemoney/schema/' );

		$this->assertTrue( $registry->has( 'Company' ) );
		$this->assertTrue( $registry->has( 'Person' ) );

		$this->assertFalse( $registry->has( 'Ivan Sraka' ) );
	}

	/**
	 * @dataProvider GetSchemaProvider
	 */
	public function testGetSchema( string $schema_name, string $label ) : void {
		$registry = new YamlSchemaRegistry( 'followthemoney/followthemoney/schema/' );

		$schema = $registry->get( $schema_name );

		$this->assertArrayHasKey( 'label', $schema );
		$this->assertArrayHasKey( 'caption', $schema );
		$this->assertArrayHasKey( 'properties', $schema );

		$this->assertSame( $label, $schema[ 'label' ] ?? null );
	}

	public function GetSchemaProvider() : array {
		return [
			['Address', 'Address'],
			['HyperText', 'Web page'],
			['ProjectParticipant', 'Project participant'],
		];
	}

	public function testGettingUndefinedSchemaThrowsException() : void {
		$registry = new YamlSchemaRegistry( 'followthemoney/followthemoney/schema/' );

		$this->expectException( FtmSchemaException::class );
		$registry->get( 'foo bar' );
	}
}
