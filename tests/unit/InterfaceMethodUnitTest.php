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
 * @covers \SebastianBergmann\CodeUnit\InterfaceMethodUnit
 * @covers \SebastianBergmann\CodeUnit\CodeUnit
 *
 * @testdox InterfaceMethodUnit
 */
final class InterfaceMethodUnitTest extends TestCase
{
    public function testCanBeCreatedFromNameOfUserDefinedInterfaceAndMethodName(): void
    {
        $unit = CodeUnit::forInterfaceMethod(FixtureInterface::class, 'method');

        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertTrue($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureInterface::class . '::method', $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/FixtureInterface.php'), $unit->sourceFileName());
        $this->assertSame([14], $unit->sourceLines());
    }

    public function testCannotBeCreatedForMethodOfInternalInterface(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forInterfaceMethod(\Iterator::class, 'current');
    }

    public function testCannotBeCreatedForClassMethod(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forInterfaceMethod(FixtureClass::class, 'publicMethod');
    }

    public function testCannotBeCreatedForTraitMethod(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forInterfaceMethod(FixtureTrait::class, 'method');
    }
}
