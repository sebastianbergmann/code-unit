<?php declare(strict_types=1);
/*
 * This file is part of sebastian/code-unit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\CodeUnit;

final class CodeUnitCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var CodeUnit[]
     */
    private $codeUnits = [];

    /**
     * @param CodeUnit[] $items
     */
    public static function fromArray(array $items): self
    {
        $collection = new self;

        foreach ($items as $item) {
            $collection->add($item);
        }

        return $collection;
    }

    public static function fromList(CodeUnit ...$items): self
    {
        return self::fromArray($items);
    }

    /**
     * @throws InvalidCodeUnitException
     * @throws ReflectionException
     */
    public static function fromString(string $unit): self
    {
        if (\strpos($unit, '::') !== false) {
            [$firstPart, $secondPart] = \explode('::', $unit);

            if (empty($firstPart) && \function_exists($secondPart)) {
                return self::fromList(CodeUnit::forFunction($secondPart));
            }

            if (\class_exists($firstPart)) {
                if ($secondPart === '<public>') {
                    return self::publicMethodsOfClass($firstPart);
                }

                if ($secondPart === '<!public>') {
                    return self::protectedAndPrivateMethodsOfClass($firstPart);
                }

                if ($secondPart === '<protected>') {
                    return self::protectedMethodsOfClass($firstPart);
                }

                if ($secondPart === '<!protected>') {
                    return self::publicAndPrivateMethodsOfClass($firstPart);
                }

                if ($secondPart === '<private>') {
                    return self::privateMethodsOfClass($firstPart);
                }

                if ($secondPart === '<!private>') {
                    return self::publicAndProtectedMethodsOfClass($firstPart);
                }

                return self::fromList(CodeUnit::forClassMethod($firstPart, $secondPart));
            }

            if (\interface_exists($firstPart)) {
                return self::fromList(CodeUnit::forInterfaceMethod($firstPart, $secondPart));
            }

            if (\trait_exists($firstPart)) {
                return self::fromList(CodeUnit::forTraitMethod($firstPart, $secondPart));
            }
        } else {
            if (\class_exists($unit)) {
                $units = [CodeUnit::forClass($unit)];

                foreach (self::reflectorForClass($unit)->getTraits() as $trait) {
                    $units[] = CodeUnit::forTrait($trait->getName());
                }

                return self::fromArray($units);
            }

            if (\interface_exists($unit)) {
                return self::fromList(CodeUnit::forInterface($unit));
            }

            if (\trait_exists($unit)) {
                return self::fromList(CodeUnit::forTrait($unit));
            }

            if (\function_exists($unit)) {
                return self::fromList(CodeUnit::forFunction($unit));
            }

            if (\strpos($unit, '<extended>') !== false) {
                $unit = \str_replace('<extended>', '', $unit);

                if (\class_exists($unit)) {
                    return self::classAndParentClasses($unit);
                }
            }
        }

        throw new InvalidCodeUnitException(
            \sprintf(
                '"%s" is not a valid code unit',
                $unit
            )
        );
    }

    private function __construct()
    {
    }

    /**
     * @return CodeUnit[]
     */
    public function asArray(): array
    {
        return $this->codeUnits;
    }

    public function getIterator(): CodeUnitCollectionIterator
    {
        return new CodeUnitCollectionIterator($this);
    }

    public function count(): int
    {
        return \count($this->codeUnits);
    }

    public function isEmpty(): bool
    {
        return empty($this->codeUnits);
    }

    /**
     * @psalm-return array<string,array<int,int>>
     */
    public function sourceLines(): array
    {
        $result = [];

        foreach ($this as $codeUnit) {
            $sourceFileName = $codeUnit->sourceFileName();

            if (!isset($result[$sourceFileName])) {
                $result[$sourceFileName] = [];
            }

            $result[$sourceFileName] = \array_merge($result[$sourceFileName], $codeUnit->sourceLines());
        }

        foreach (\array_keys($result) as $sourceFileName) {
            $result[$sourceFileName] = \array_unique($result[$sourceFileName]);

            \sort($result[$sourceFileName]);
        }

        \ksort($result);

        return $result;
    }

    public function mergeWith(self $other): self
    {
        return self::fromArray(
            \array_merge(
                $this->asArray(),
                $other->asArray()
            )
        );
    }

    private function add(CodeUnit $item): void
    {
        $this->codeUnits[] = $item;
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private static function publicMethodsOfClass(string $className): self
    {
        return self::methodsOfClass($className, \ReflectionMethod::IS_PUBLIC);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private static function publicAndProtectedMethodsOfClass(string $className): self
    {
        return self::methodsOfClass($className, \ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private static function publicAndPrivateMethodsOfClass(string $className): self
    {
        return self::methodsOfClass($className, \ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PRIVATE);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private static function protectedMethodsOfClass(string $className): self
    {
        return self::methodsOfClass($className, \ReflectionMethod::IS_PROTECTED);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private static function protectedAndPrivateMethodsOfClass(string $className): self
    {
        return self::methodsOfClass($className, \ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PRIVATE);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private static function privateMethodsOfClass(string $className): self
    {
        return self::methodsOfClass($className, \ReflectionMethod::IS_PRIVATE);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private static function methodsOfClass(string $className, int $filter): self
    {
        $units = [];

        foreach (self::reflectorForClass($className)->getMethods($filter) as $method) {
            $units[] = CodeUnit::forClassMethod($className, $method->getName());
        }

        return self::fromArray($units);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private static function classAndParentClasses($className): self
    {
        $units = [CodeUnit::forClass($className)];

        $reflector = self::reflectorForClass($className);

        foreach (self::reflectorForClass($className)->getTraits() as $trait) {
            $units[] = CodeUnit::forTrait($trait->getName());
        }

        while ($reflector = $reflector->getParentClass()) {
            $units[] = CodeUnit::forClass($reflector->getName());

            foreach (self::reflectorForClass($className)->getTraits() as $trait) {
                $units[] = CodeUnit::forTrait($trait->getName());
            }
        }

        return self::fromArray($units);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws ReflectionException
     */
    private static function reflectorForClass(string $className): \ReflectionClass
    {
        try {
            return new \ReflectionClass($className);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
        // @codeCoverageIgnoreEnd
    }
}
