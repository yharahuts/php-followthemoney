<?php
namespace FollowTheMoney;

/**
 * Property value types, as defined in FtM.
 */
enum PropertyType : string {
	case Address = 'address';
	case Checksum = 'checksum';
	case Country = 'country';
	case Date = 'date';
	case Email = 'email';
	case Entity = 'entity';
	case Gender = 'gender';
	case Html = 'html';
	case Iban = 'iban';

	// type of entity id statement only, not used in schemas
	case Id = 'id';
	case Identifier = 'identifier';
	case Ip = 'ip';
	case Json = 'json';
	case Language = 'language';
	case Mimetype = 'mimetype';
	case Name = 'name';
	case Number = 'number';
	case Phone = 'phone';
	case String = 'string';
	case Text = 'text';
	case Topic = 'topic';
	case Url = 'url';
}
