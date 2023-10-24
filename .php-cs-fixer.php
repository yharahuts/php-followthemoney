<?php
require_once __DIR__ . '/vendor/tareq1988/wp-php-cs-fixer/loader.php';
require_once __DIR__ . '/vendor/yharahuts/space-inside-array-braces-fixer/src/SpaceInsideArrayBracesFixer.php';

$finder = PhpCsFixer\Finder::create()
	->exclude( 'vendor' )
	->exclude( 'bin' )
	->in( __DIR__ );

$config = new PhpCsFixer\Config();

return $config
	->registerCustomFixers( [
		new \WeDevs\Fixer\SpaceInsideParenthesisFixer(),
		new \Codestyle\Fixer\SpaceInsideArrayBracesFixer(),
	] )
	->setRules( [
		'@PhpCsFixer'                         => true,
		'indentation_type'                    => true,
		'array_syntax'                        => [ 'syntax' => 'short' ],
		'curly_braces_position'               => [
			'functions_opening_brace'               => 'same_line',
			'classes_opening_brace'                 => 'same_line',
			'allow_single_line_anonymous_functions' => true,
		],
		'no_spaces_around_offset'             => [ 'positions' => [ 'outside' ] ],
		'return_type_declaration'             => [ 'space_before' => 'one' ],
		'explicit_indirect_variable'          => true,
		'cast_spaces'                         => true,
		'binary_operator_spaces'              => [
			'default'   => 'single_space',
			'operators' => [ '=>' => 'align_single_space_minimal' ],
		],
		'yoda_style'                          => [
			'equal'                => false,
			'identical'            => false,
			'less_and_greater'     => false,
			'always_move_variable' => true,
		],
		'elseif'                              => false,
		'no_superfluous_elseif'               => false,
		'phpdoc_add_missing_param_annotation' => true,
		'phpdoc_align'                        => [ 'align' => 'left' ],
		'phpdoc_no_alias_tag'                 => [ 'replacements' => [ 'type' => 'var', 'link' => 'see' ] ],
		'phpdoc_types_order'                  => [
			'null_adjustment' => 'always_last',
			'sort_algorithm'  => 'alpha',
		],
		'phpdoc_to_comment'                   => [ 'ignored_tags' => [ 'var', 'todo', 'psalm-suppress' ] ],
		'combine_consecutive_issets'          => false,
		'no_leading_namespace_whitespace'     => false,
		'no_superfluous_phpdoc_tags'          => false,
		'blank_lines_before_namespace'        => [ 'min_line_breaks' => 1, 'max_line_breaks' => 1 ],
		'php_unit_test_class_requires_covers' => false,
		'WeDevs/space_inside_parenthesis'     => true,
		'Yharahuts/space_inside_array_braces'     => true,
		'class_attributes_separation'         => [
			'elements' => [
				'const'        => 'only_if_meta',
				'method'       => 'one',
				'property'     => 'only_if_meta',
				'trait_import' => 'none',
				'case'         => 'none',
			],
		],
	] )
	->setIndent( "\t" )
	->setLineEnding( "\n" )
	->setFinder( $finder );