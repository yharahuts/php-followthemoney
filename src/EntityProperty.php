<?php
namespace FollowTheMoney;

class EntityProperty {
	public string $name;
	public PropertyType $type;
	public ?string $label;
	public ?string $description;

	public function __construct( string $name, PropertyType $type, ?string $label = null, ?string $description = null ) {
		$this->name = $name;
		$this->type = $type;
		$this->label = $label;
		$this->description = $description;
	}
}
