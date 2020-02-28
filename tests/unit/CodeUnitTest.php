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

use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeUnit\Fixture\FixtureClass;
use SebastianBergmann\CodeUnit\Fixture\FixtureInterface;
use SebastianBergmann\CodeUnit\Fixture\FixtureTrait;

/**
 * @covers \SebastianBergmann\CodeUnit\CodeUnit
 * @covers \SebastianBergmann\CodeUnit\ClassUnit
 * @covers \SebastianBergmann\CodeUnit\ClassMethodUnit
 * @covers \SebastianBergmann\CodeUnit\InterfaceUnit
 * @covers \SebastianBergmann\CodeUnit\InterfaceMethodUnit
 * @covers \SebastianBergmann\CodeUnit\TraitUnit
 * @covers \SebastianBergmann\CodeUnit\TraitMethodUnit
 * @covers \SebastianBergmann\CodeUnit\FunctionUnit
 */
final class CodeUnitTest extends TestCase
{
    public function testCanBeCreatedFromNameOfUserDefinedClass(): void
    {
        $unit = CodeUnit::forClass(FixtureClass::class);

        $this->assertTrue($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureClass::class, $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/FixtureClass.php'), $unit->sourceFileName());
        $this->assertSame(\range(12, 18), $unit->sourceLines());
    }

    public function testCanBeCreatedFromNameOfUserDefinedClassAndMethodName(): void
    {
        $unit = CodeUnit::forClassMethod(FixtureClass::class, 'method');

        $this->assertTrue($unit->isClassMethod());
        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureClass::class . '::method', $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/FixtureClass.php'), $unit->sourceFileName());
        $this->assertSame(\range(14, 17), $unit->sourceLines());
    }

    public function testCanBeCreatedFromNameOfUserDefinedInterface(): void
    {
        $unit = CodeUnit::forInterface(FixtureInterface::class);

        $this->assertTrue($unit->isInterface());
        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureInterface::class, $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/FixtureInterface.php'), $unit->sourceFileName());
        $this->assertSame(\range(12, 15), $unit->sourceLines());
    }

    public function testCanBeCreatedFromNameOfUserDefinedInterfaceAndMethodName(): void
    {
        $unit = CodeUnit::forInterfaceMethod(FixtureInterface::class, 'method');

        $this->assertTrue($unit->isInterfaceMethod());
        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureInterface::class . '::method', $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/FixtureInterface.php'), $unit->sourceFileName());
        $this->assertSame([14], $unit->sourceLines());
    }

    public function testCanBeCreatedFromNameOfUserDefinedTrait(): void
    {
        $unit = CodeUnit::forTrait(FixtureTrait::class);

        $this->assertTrue($unit->isTrait());
        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureTrait::class, $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/FixtureTrait.php'), $unit->sourceFileName());
        $this->assertSame(\range(12, 18), $unit->sourceLines());
    }

    public function testCanBeCreatedFromNameOfUserDefinedTraitAndMethodName(): void
    {
        $unit = CodeUnit::forTraitMethod(FixtureTrait::class, 'method');

        $this->assertTrue($unit->isTraitMethod());
        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureTrait::class . '::method', $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/FixtureTrait.php'), $unit->sourceFileName());
        $this->assertSame(\range(14, 17), $unit->sourceLines());
    }

    public function testCanBeCreatedFromNameOfUserDefinedFunction(): void
    {
        $unit = CodeUnit::forFunction('SebastianBergmann\CodeUnit\Fixture\f');

        $this->assertTrue($unit->isFunction());
        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());

        $this->assertSame('SebastianBergmann\CodeUnit\Fixture\f', $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/function.php'), $unit->sourceFileName());
        $this->assertSame(\range(12, 15), $unit->sourceLines());
    }

    public function testCannotBeCreatedFromNameOfInternalClass(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forClass(\Exception::class);
    }

    public function testCannotBeCreatedFromNameOfInternalMethod(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forClassMethod(\Exception::class, 'getMessage');
    }

    public function testCannotBeCreatedFromNameOfInternalFunction(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forFunction('abs');
    }
}
