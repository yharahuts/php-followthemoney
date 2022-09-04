<?php
	namespace FollowTheMoney;

	class EntityProperty {
		public string $name;
		public ?string $label;
		public ?string $description;
		public ?string $type;

		/**
		 * EntityProperty constructor.
		 * @param string $name
		 * @param string|null $label
		 * @param string|null $description
		 * @param string|null $type
		 */
		public function __construct( string $name, ?string $label = null, ?string $description = null, ?string $type = null ) {
			$this->name = $name;
			$this->label = $label;
			$this->description = $description;
			$this->type = $type;
		}
	}