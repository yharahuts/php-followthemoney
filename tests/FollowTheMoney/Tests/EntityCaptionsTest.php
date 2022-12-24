<?php
	namespace FollowTheMoney\Tests;

	use FollowTheMoney\EntitySchema;
	use PHPUnit\Framework\TestCase;

	class EntityCaptionsTest extends TestCase {
		public function CaptionsDataProvider( ) {
			return [
				'company-20064120'        => [ 'company/company-20064120.json', 'ВРУ' ],
				'company-foo-bar'         => [ 'company/company-foo-bar.json', 'foo bar' ],
				'company-with-empty-name' => [ 'company/company-with-empty-name.json', null ],
				'company-with-no-name'    => [ 'company/company-with-no-name.json', null ],
				'person-ivan-sraka'       => [ 'etc/person-ivan-sraka.json', 'Іван Срака' ],
				'person-with-email'       => [ 'etc/person-with-email.json', 'ivan@sraka.com' ],
				'person-with-empty-email' => [ 'etc/person-with-empty-email.json', 'ivan@sraka.com' ],
			];
		}

		/**
		 * @dataProvider CaptionsDataProvider
		 * @covers EntitySchema::getPropertyLabel()
		 */
		public function testEntityCaptions( string $json_file, ?string $expected_caption ) {
			$json = file_get_contents( "tests/data/{$json_file}" );

			$entity = EntitySchema::fromJson( $json, 'followthemoney/followthemoney/schema/' );
			$this->assertSame( $expected_caption, $entity->getEntityCaption( ) );
		}
	}