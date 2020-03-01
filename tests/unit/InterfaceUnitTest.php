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
 * @covers \SebastianBergmann\CodeUnit\InterfaceUnit
 * @covers \SebastianBergmann\CodeUnit\CodeUnit
 *
 * @testdox InterfaceUnit
 */
final class InterfaceUnitTest extends TestCase
{
    public function testCanBeCreatedFromNameOfUserDefinedInterface(): void
    {
        $unit = CodeUnit::forInterface(FixtureInterface::class);

        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertTrue($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureInterface::class, $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/FixtureInterface.php'), $unit->sourceFileName());
        $this->assertSame(\range(12, 15), $unit->sourceLines());
    }

    public function testCannotBeCreatedForInternalInterface(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forInterface(\Iterator::class);
    }

    public function testCannotBeCreatedForClass(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forInterface(FixtureClass::class);
    }

    public function testCannotBeCreatedForTrait(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forInterface(FixtureTrait::class);
    }
}
