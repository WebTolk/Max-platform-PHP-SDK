<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,
        'array_indentation' => true,
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'continue',
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'return',
                'throw',
                'use',
            ],
        ],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'single_blank_line_at_eof' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
            ],
        ],
    ])
    ->setFinder($finder);
