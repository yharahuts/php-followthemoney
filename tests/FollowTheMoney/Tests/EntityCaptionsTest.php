<?php
namespace Tests\FollowTheMoney\Tests;

use FollowTheMoney\EntitySchema;
use PHPUnit\Framework\TestCase;
use Tests\Support\SchemaRepositoryAware;

/**
 * @internal
 */
final class EntityCaptionsTest extends TestCase {
	use SchemaRepositoryAware;

	public function CaptionsDataProvider() : array {
		return [
			'company-20064120'        => [ 'company/company-20064120.json', 'ВРУ' ],
			'company-foo-bar'         => [ 'company/company-foo-bar.json', 'foo bar' ],
			'company-with-empty-name' => [ 'company/company-with-empty-name.json', null ],
			'company-with-no-name'    => [ 'company/company-with-no-name.json', null ],
			'person-ivan-sraka'       => [ 'etc/person-ivan-sraka.json', 'Іван Срака' ],
			'person-with-email'       => [ 'etc/person-with-email.json', 'ivan@sraka.com' ],
			'person-with-empty-email' => [ 'etc/person-with-empty-email.json', 'ivan@sraka.com' ],
			'edge-debt'               => [ 'etc/edge-debt.json', '123' ],
		];
	}

	/**
	 * @dataProvider CaptionsDataProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getEntityCaption()
	 */
	public function testEntityCaptions( string $json_file, ?string $expected_caption ) {
		$json = file_get_contents( "tests/data/{$json_file}" );

		$entity = EntitySchema::fromJson( $json, $this->getRegistry() );
		$this->assertSame( $expected_caption, $entity->getEntityCaption() );
	}

	public function CaptionPropertiesNamesProvider() : array {
		return [
			'company-20064120'  => [ 'company/company-20064120.json', [ 'name' ] ],
			'person-ivan-sraka' => [ 'etc/person-ivan-sraka.json', [ 'name', 'lastName', 'email', 'phone' ] ],
		];
	}

	/**
	 * @dataProvider  CaptionPropertiesNamesProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getCaptionPropertiesNames
	 */
	public function testEntityCaptionPropertiesNames( string $json_file, array $expected_properties ) {
		$json = file_get_contents( "tests/data/{$json_file}" );

		$entity = EntitySchema::fromJson( $json, $this->getRegistry() );
		$this->assertSame( $expected_properties, $entity->getCaptionPropertiesNames() );
	}

	public function CaptionPropertiesValuesProvider() : array {
		return [
			'company-20064120'  => [ 'company/company-20064120.json', [ 'name' => [ 'ВРУ', 'Верховна Рада України' ] ] ],
			'person-ivan-sraka' => [ 'etc/person-ivan-sraka.json', [ 'name' => [ 'Іван Срака', 'Sraka Ivan' ], 'email' => [ 'ivan@sraka.com' ] ] ],
			'edge-debt'         => [ 'etc/edge-debt.json', [ 'amount' => [ 123.0 ] ] ],
		];
	}

	/**
	 * @dataProvider  CaptionPropertiesValuesProvider
	 *
	 * @covers \FollowTheMoney\EntitySchema::getCaptionValues
	 */
	public function testEntityCaptionPropertiesValues( string $json_file, array $expected_values ) {
		$json = file_get_contents( "tests/data/{$json_file}" );

		$entity = EntitySchema::fromJson( $json, $this->getRegistry() );
		$this->assertSame( $expected_values, $entity->getCaptionValues() );
	}
}
