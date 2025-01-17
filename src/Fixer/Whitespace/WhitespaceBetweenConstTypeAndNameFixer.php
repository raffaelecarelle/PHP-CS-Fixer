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

namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 *
 * @author Raffaele Carelle <raffaele.carelle@gmail.com>
 */
final class WhitespaceBetweenConstTypeAndNameFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'A single space should be between constant type and constant name.',
            [
                new CodeSample(
                    "<?php\nclass Example\n{\npublic const string      FOO = 1;\n}\n"
                ),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return PHP_VERSION_ID >= 80300 && $tokens->isTokenKindFound(T_CONST);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokenCount = $tokens->count() - 1;

        for ($index = 0; $index < $tokenCount; ++$index) {
            if ($tokens[$index]->isGivenKind(T_CONST)) {
                // Type hint?
                $typehintIdex = $tokens->getNextMeaningfulToken($index);

                if ($tokens[$typehintIdex]->isGivenKind(CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_OPEN)) {
                    $typehintIdex++;
                    while ($tokens[$typehintIdex]->isGivenKind(CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE)) {
                        $typehintIdex += 1;
                    }

                    if ($tokens[$typehintIdex]->isGivenKind(CT::T_TYPE_ALTERNATION)) {
                        $typehintIdex++;
                        while ($tokens[$typehintIdex]->isGivenKind(CT::T_TYPE_ALTERNATION)) {
                            $typehintIdex += 1;
                        }
                    }
                }

                // const name?
                $constNameIndex = $tokens->getNextMeaningfulToken($typehintIdex);

                while (
                    (isset($tokens[$constNameIndex]) && $tokens[$constNameIndex]->isGivenKind(CT::T_TYPE_ALTERNATION)) &&
                    (isset($tokens[$constNameIndex]) && $tokens[$constNameIndex]->isGivenKind(CT::T_TYPE_INTERSECTION))
                ) {
                    dump('aaaaaa');
                    dump($tokens[$constNameIndex]);
                    $constNameIndex += 1;
                }

                if (
                    (isset($tokens[$typehintIdex]) && $tokens[$typehintIdex]->isGivenKind(T_STRING)) &&
                    (isset($tokens[$constNameIndex]) && $tokens[$constNameIndex]->isGivenKind(T_STRING))) {
                    $tokenCount += $this->fixSpacing($tokens, $typehintIdex);
                }
            }
        }
    }

    private function fixSpacing(Tokens $tokens, int $index): int
    {
        $addedTokenCount = 0;
        $addedTokenCount += $this->ensureSingleSpace($tokens, $index + 1, 0);
        $addedTokenCount += $this->ensureSingleSpace($tokens, $index - 1, 1);

        return $addedTokenCount;
    }

    private function ensureSingleSpace(Tokens $tokens, int $index, int $offset): int
    {
        if (!$tokens[$index]->isWhitespace()) {
            $tokens->insertSlices([$index + $offset => new Token([T_WHITESPACE, ' '])]);

            return 1;
        }

        if (' ' !== $tokens[$index]->getContent() && !Preg::match('/\R/', $tokens[$index]->getContent())) {
            $tokens[$index] = new Token([T_WHITESPACE, ' ']);
        }

        return 0;
    }
}
