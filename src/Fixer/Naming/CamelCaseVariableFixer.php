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

namespace PhpCsFixer\Fixer\Naming;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

final class CamelCaseVariableFixer extends AbstractFixer
{
    /**
     * @see https://regex101.com/r/OtFn8I/1
     */
    private const PARAM_NAME_REGEX = '#(?<prefix>@(param|var)\s.*\s+\$)(?<name>[a-zA-Z_x7f-xff][a-zA-Z0-9_x7f-xff]*)#ms';

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Variables must be camel case.',
            [new CodeSample("<?php \$firstNаmе = 'Raffaele';\n")],
            null,
            'Some uses of instance variables may elude analysis.'
        );
    }

    /**
     * Must run after PhpdocLineSpanFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_DOC_COMMENT)
            || $tokens->isTokenKindFound(T_VARIABLE)
            || $tokens->isTokenKindFound(T_STRING);
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            // Fix @param and @var on doc block
            if ($tokens[$index]->isGivenKind(T_DOC_COMMENT)) {
                $phpDocContent = $tokens[$index]->getContent();

                if ($tokensAnalyzer->isSuperGlobal($index)) {
                    continue;
                }

                if (!Preg::match(self::PARAM_NAME_REGEX, $phpDocContent, $matches)) {
                    continue;
                }

                $var = $matches['name'];
                \assert(\is_string($var));

                $camelCaseName = $this->camelCase($var);

                $newContent = str_replace($var, $camelCaseName, $phpDocContent);

                if ($newContent === $tokens[$index]->getContent()) {
                    continue;
                }

                $tokens[$index] = new Token([T_DOC_COMMENT, $newContent]);
            }

            // Fix local variables
            if ($tokens[$index]->isGivenKind(T_VARIABLE)) {
                $content = $tokens[$index]->getContent();

                \assert(\is_string($content));

                if ($tokensAnalyzer->isSuperGlobal($index)) {
                    continue;
                }

                $camelCaseName = $this->camelCase($content);

                if ($camelCaseName === $content) {
                    continue;
                }
                $tokens[$index] = new Token([T_VARIABLE, '$'.$camelCaseName]);
            }

            // Fix instances variables
            if ($tokens[$index]->isGivenKind(T_STRING)) {
                $prevIndex = $tokens->getPrevMeaningfulToken($index);
                $prevToken = $tokens[$prevIndex];

                $nextIndex = $tokens->getNextMeaningfulToken($index);
                $nextToken = $tokens[$nextIndex];

                if (T_OBJECT_OPERATOR === $prevToken->getId()
                    && (
                        T_OBJECT_OPERATOR === $nextToken->getId()
                        || T_NULLSAFE_OBJECT_OPERATOR === $nextToken->getId()
                        || T_WHITESPACE === $nextToken->getId()
                        || '=' === $nextToken->getContent()
                        || ';' === $nextToken->getContent()
                        || ':' === $nextToken->getContent()
                        || ')' === $nextToken->getContent()
                        || '}' === $nextToken->getContent()
                        || '?' === $nextToken->getContent()
                        || '??' === $nextToken->getContent()
                        || '[' === $nextToken->getContent()
                    )
                ) {
                    $content = $tokens[$index]->getContent();

                    \assert(\is_string($content));

                    $camelCaseName = $this->camelCase($content);

                    if ($camelCaseName === $content) {
                        continue;
                    }

                    $tokens[$index] = new Token([T_STRING, $camelCaseName]);
                }
            }
        }
    }

    private function camelCase(string $input): string
    {
        $dollarFirst = false;
        if (str_starts_with($input, '$')) {
            $input = ltrim($input, '$');
            $dollarFirst = true;
        }

        $output = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));

        return Preg::replace('#\W#', '', $dollarFirst ? '$'.$output : $output);
    }
}
