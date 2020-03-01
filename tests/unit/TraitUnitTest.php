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
 * @covers \SebastianBergmann\CodeUnit\TraitUnit
 * @covers \SebastianBergmann\CodeUnit\CodeUnit
 *
 * @testdox TraitUnit
 */
final class TraitUnitTest extends TestCase
{
    public function testCanBeCreatedFromNameOfUserDefinedTrait(): void
    {
        $unit = CodeUnit::forTrait(FixtureTrait::class);

        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertTrue($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureTrait::class, $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/FixtureTrait.php'), $unit->sourceFileName());
        $this->assertSame(\range(12, 18), $unit->sourceLines());
    }

    public function testCannotBeCreatedForClass(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forTrait(FixtureClass::class);
    }

    public function testCannotBeCreatedForInterface(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forTrait(FixtureInterface::class);
    }
}
