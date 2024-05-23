<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Fixer\Naming;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @author Raffaele Carelle <raffaele.carelle@gmail.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\Naming\CamelCaseVariableFixer
 */
final class CamelCaseVariableFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    public static function provideFixCases(): iterable
    {
        yield [
            '<?php $testVariable = 1;',
            '<?php $test_variable = 1;',
        ];

        yield [
            '<?php $testVariable ?? 1;',
            '<?php $test_variable ?? 1;',
        ];

        yield [
            '<?php $testVariable ? -1 : 1;',
            '<?php $test_variable ? -1 : 1;',
        ];

        yield [
            '<?php $this->testVariable = 1;',
            '<?php $this->test_variable = 1;',
        ];

        yield [
            '<?php $this->testVariable->method();',
            '<?php $this->test_variable->method();',
        ];

        yield [
            '<?php $this->testVariable?->method();',
            '<?php $this->test_variable?->method();',
        ];

        yield [
            '<?php $testVariable->method();',
            '<?php $test_variable->method();',
        ];

        yield [
            '<?php $testVariable?->method();',
            '<?php $test_variable?->method();',
        ];

        yield [
            '<?php echo "Print variable: {$testVariable}";',
            '<?php echo "Print variable: {$test_variable}";',
        ];

        yield [
            '<?php $this->testVariable ?? -1;',
            '<?php $this->test_variable ?? -1;',
        ];

        yield [
            '<?php $this->testVariable ? -1 : 1;',
            '<?php $this->test_variable ? -1 : 1;',
        ];

        yield [
            '<?php echo "Print variable: {$this->testVariable}";',
            '<?php echo "Print variable: {$this->test_variable}";',
        ];

        yield [
            '<?php /** @var string $testVariable */',
            '<?php /** @var string $test_variable */',
        ];

        yield [
            '<?php /** @param string $testVariable */',
            '<?php /** @param string $test_variable */',
        ];

        yield [
            '<?php class Foo { private readonly string $testVariable; }',
            '<?php class Foo { private readonly string $test_variable; }',
        ];

        yield [
            '<?php class Foo { private string $testVariable; }',
            '<?php class Foo { private string $test_variable; }',
        ];

        yield [
            '<?php class Foo { private $testVariable; }',
            '<?php class Foo { private $test_variable; }',
        ];

        yield [
            '<?php class Foo { var $testVariable; }',
            '<?php class Foo { var $_test_variable; }',
        ];

        yield [
            '<?php $testVariable[] = 1;',
            '<?php $test_variable[] = 1;',
        ];

        yield [
            '<?php $this->testVariable[] = 1;',
            '<?php $this->test_variable[] = 1;',
        ];
    }
}
