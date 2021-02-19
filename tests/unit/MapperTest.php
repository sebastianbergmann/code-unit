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

use function range;
use function realpath;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeUnit\Fixture\FixtureAnotherChildClass;
use SebastianBergmann\CodeUnit\Fixture\FixtureAnotherParentClass;
use SebastianBergmann\CodeUnit\Fixture\FixtureClass;
use SebastianBergmann\CodeUnit\Fixture\FixtureClassWithTrait;
use SebastianBergmann\CodeUnit\Fixture\FixtureInterface;
use SebastianBergmann\CodeUnit\Fixture\FixtureTrait;
use SebastianBergmann\CodeUnit\Fixture\Getopt;

/**
 * @covers \SebastianBergmann\CodeUnit\Mapper
 *
 * @uses \SebastianBergmann\CodeUnit\CodeUnit
 * @uses \SebastianBergmann\CodeUnit\CodeUnitCollection
 * @uses \SebastianBergmann\CodeUnit\CodeUnitCollectionIterator
 */
final class MapperTest extends TestCase
{
    /**
     * @testdox Can map 'function_name' string to code unit objects
     */
    public function testCanMapStringWithFunctionNameToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits('SebastianBergmann\CodeUnit\Fixture\f');

        $this->assertSame('SebastianBergmann\CodeUnit\Fixture\f', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can map '::function_name' string to code unit objects
     */
    public function testCanMapStringWithFunctionNamePrefixedWithDoubleColonsToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits('::f');

        $this->assertSame('f', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can map 'ClassName' string to code unit objects
     */
    public function testCanMapStringWithClassNameToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits(FixtureClass::class);

        $this->assertCount(1, $units);
        $this->assertSame(FixtureClass::class, $units->asArray()[0]->name());
    }

    /**
     * @testdox Can map 'ClassName' string to code unit objects
     */
    public function testMapClassesAndTheTraitsTheyUseToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits(FixtureClassWithTrait::class);

        $this->assertCount(2, $units);
        $this->assertSame(FixtureClassWithTrait::class, $units->asArray()[0]->name());
        $this->assertSame(FixtureTrait::class, $units->asArray()[1]->name());
    }

    /**
     * @testdox Can map 'ClassName::methodName' string to code unit objects
     */
    public function testCanMapStringWithClassNameAndMethodNameToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits(FixtureClass::class . '::publicMethod');

        $this->assertSame(FixtureClass::class . '::publicMethod', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can map 'InterfaceName' string to code unit objects
     */
    public function testCanMapStringWithInterfaceNameToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits(FixtureInterface::class);

        $this->assertSame(FixtureInterface::class, $units->asArray()[0]->name());
    }

    /**
     * @testdox Can map 'InterfaceName::methodName' string to code unit objects
     */
    public function testCanMapStringWithInterfaceNameAndMethodNameToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits(FixtureInterface::class . '::method');

        $this->assertSame(FixtureInterface::class . '::method', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can map 'TraitName' string to code unit objects
     */
    public function testCanMapStringWithTraitNameToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits(FixtureTrait::class);

        $this->assertSame(FixtureTrait::class, $units->asArray()[0]->name());
    }

    /**
     * @testdox Can map 'TraitName::methodName' string to code unit objects
     */
    public function testCanMapStringWithTraitNameAndMethodNameToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits(FixtureTrait::class . '::method');

        $this->assertSame(FixtureTrait::class . '::method', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can map 'file::[[absolute-path]]' string to code unit objects
     */
    public function testCanMapStringWithFileNameToCodeUnitObjects(): void
    {
        $units = (new Mapper)->stringToCodeUnits('file:' . realpath(__FILE__));

        $this->assertSame('file:' . realpath(__FILE__), $units->asArray()[0]->name());
    }

    public function testCannotMapInvalidStringToCodeUnitObjects(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        (new Mapper)->stringToCodeUnits('invalid');
    }

    public function testCanMapCodeUnitObjectsToArrayWithSourceLinesInSingleFile(): void
    {
        $codeUnits = CodeUnitCollection::fromList(
            CodeUnit::forFunction('SebastianBergmann\CodeUnit\Fixture\another_function'),
            CodeUnit::forClass(FixtureAnotherParentClass::class),
            CodeUnit::forClass(FixtureAnotherChildClass::class),
        );

        $this->assertSame(
            [
                realpath(__DIR__ . '/../_fixture/file_with_multiple_code_units.php') => [
                    12,
                    13,
                    14,
                    16,
                    17,
                    18,
                    20,
                    21,
                    22,
                ],
            ],
            (new Mapper)->codeUnitsToSourceLines($codeUnits)
        );
    }

    public function testCanMapCodeUnitObjectsToArrayWithSourceLinesInMultipleFiles(): void
    {
        $codeUnits = CodeUnitCollection::fromList(
            CodeUnit::forInterface(FixtureInterface::class),
            CodeUnit::forClass(FixtureClass::class),
            CodeUnit::forClass(FixtureClass::class)
        );

        $this->assertSame(
            [
                realpath(__DIR__ . '/../_fixture/FixtureClass.php')     => range(12, 28),
                realpath(__DIR__ . '/../_fixture/FixtureInterface.php') => range(12, 15),
            ],
            (new Mapper)->codeUnitsToSourceLines($codeUnits)
        );
    }

    public function testCanMapOverlappingCodeUnitObjectsToArrayWithSourceLines(): void
    {
        $codeUnits = CodeUnitCollection::fromList(
            CodeUnit::forClass(FixtureClass::class),
            CodeUnit::forClassMethod(FixtureClass::class, 'publicMethod'),
            CodeUnit::forClassMethod(FixtureClass::class, 'protectedMethod'),
            CodeUnit::forClassMethod(FixtureClass::class, 'privateMethod'),
        );

        $this->assertSame(
            [
                realpath(__DIR__ . '/../_fixture/FixtureClass.php') => range(12, 28),
            ],
            (new Mapper)->codeUnitsToSourceLines($codeUnits)
        );
    }

    /**
     * @ticket https://github.com/sebastianbergmann/code-unit/issues/3
     */
    public function testIssue3(): void
    {
        $units = (new Mapper)->stringToCodeUnits(Getopt::class . '::getopt');

        $this->assertSame(Getopt::class . '::getopt', $units->asArray()[0]->name());
    }
}
