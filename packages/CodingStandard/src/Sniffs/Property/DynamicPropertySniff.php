<?php declare(strict_types=1);

namespace Symplify\CodingStandard\Sniffs\Property;

use Nette\Utils\Strings;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Symplify\TokenRunner\Wrapper\SnifferWrapper\ClassWrapper;

final class DynamicPropertySniff implements Sniff
{
    /**
     * @var string
     */
    private const ERROR_MESSAGE = 'Property "$%s" should be used instead of dynamically defined one.';

    /**
     * @var mixed[]
     */
    private $tokens = [];

    /**
     * @var File
     */
    private $file;

    /**
     * @var ClassWrapper[]
     */
    private $classWrappersPerFile = [];

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_OBJECT_OPERATOR];
    }

    /**
     * @param int $position
     */
    public function process(File $file, $position): void
    {
        $this->file = $file;
        $this->tokens = $file->getTokens();

        if (! $this->isLocalPropertyAccess($position)) {
            return;
        }

        $propertyName = $this->tokens[$position + 1]['content'];

        $classWrapper = $this->getClassWrapper();

        if (in_array($propertyName, $classWrapper->getPropertyNames(), true)) {
            return;
        }

        if ($this->isMagicProperty($propertyName, $classWrapper)) {
            return;
        }

        $file->addError(sprintf(self::ERROR_MESSAGE, $propertyName), $position, self::class);
    }

    private function isLocalPropertyAccess($position): bool
    {
        $previousToken = $this->tokens[$position - 1];
        $nextToken = $this->tokens[$position + 1];
        $nextNextToken = $this->tokens[$position + 2];

        if ($nextNextToken['code'] === T_OPEN_PARENTHESIS) {
            return false;
        }

        return $previousToken['content'] === '$this' && $nextToken['code'] === T_STRING;
    }

    private function getClassWrapper(): ClassWrapper
    {
        $filename = $this->file->getFilename();
        if (isset($this->classWrappersPerFile[$filename])) {
            return $this->classWrappersPerFile[$filename];
        }

        $classTokenPosition = $this->file->findNext([T_CLASS, T_TRAIT], 1);

        $classWrapper = ClassWrapper::createFromFileAndPosition($this->file, $classTokenPosition);

        return $this->classWrappersPerFile[$filename] = $classWrapper;
    }

    private function isMagicProperty(string $propertyName, ClassWrapper $classWrapper): bool
    {
        return $this->isNettePresenterTemplateMagicProperty($propertyName, $classWrapper);
    }

    private function isNettePresenterTemplateMagicProperty(string $propertyName, ClassWrapper $classWrapper): bool
    {
        $parentClassName = $classWrapper->getParentClassName();
        if ($parentClassName === null) {
            return false;
        }

        return $propertyName === 'template' && Strings::endsWith($parentClassName, 'Presenter');
    }
}
