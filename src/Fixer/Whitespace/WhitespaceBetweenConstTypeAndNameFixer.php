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
                    "<?php\nclass Example\n{\npublic const string FOO = 1;\n}\n"
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
        return $tokens->isAnyTokenKindsFound([T_CONST]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokenCount = $tokens->count() - 1;

//        dump($tokens);
//        dump($tokens->getNextTokenOfKind(T_CONST));
//        dump($tokens->getPrevTokenOfKind(T_CONST));

//        $tokens
        for ($index = 0; $index < $tokenCount; ++$index) {
            if($tokens[$index]->isGivenKind(T_CONST)) {
                $nextTokenIndex = $tokens->getNextMeaningfulToken($index);
                dump($tokens[$nextTokenIndex]);
                $tokenCount += $this->fixSpacing($tokens, $index);
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
