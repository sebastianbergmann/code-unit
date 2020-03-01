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
            [$type, $method] = \explode('::', $unit);

            if (empty($type)) {
                if (\function_exists($method)) {
                    return self::fromList(CodeUnit::forFunction($method));
                }
            }

            if (\class_exists($type)) {
                if ($method === '<public>') {
                    return self::publicMethodsOfClass($type);
                }

                if ($method === '<!public>') {
                    return self::protectedAndPrivateMethodsOfClass($type);
                }

                if ($method === '<protected>') {
                    return self::protectedMethodsOfClass($type);
                }

                if ($method === '<!protected>') {
                    return self::publicAndPrivateMethodsOfClass($type);
                }

                if ($method === '<private>') {
                    return self::privateMethodsOfClass($type);
                }

                if ($method === '<!private>') {
                    return self::publicAndProtectedMethodsOfClass($type);
                }

                return self::fromList(CodeUnit::forClassMethod($type, $method));
            }

            if (\interface_exists($type)) {
                return self::fromList(CodeUnit::forInterfaceMethod($type, $method));
            }

            if (\trait_exists($type)) {
                return self::fromList(CodeUnit::forTraitMethod($type, $method));
            }
        } else {
            if (\class_exists($unit)) {
                return self::fromList(CodeUnit::forClass($unit));
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
