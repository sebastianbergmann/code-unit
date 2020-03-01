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

/**
 * @covers \SebastianBergmann\CodeUnit\FunctionUnit
 * @covers \SebastianBergmann\CodeUnit\CodeUnit
 *
 * @testdox FunctionUnit
 */
final class FunctionUnitTest extends TestCase
{
    public function testCanBeCreatedFromNameOfUserFunction(): void
    {
        $unit = CodeUnit::forFunction('SebastianBergmann\CodeUnit\Fixture\f');

        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertTrue($unit->isFunction());

        $this->assertSame('SebastianBergmann\CodeUnit\Fixture\f', $unit->name());
        $this->assertSame(\realpath(__DIR__ . '/../_fixture/function.php'), $unit->sourceFileName());
        $this->assertSame(\range(12, 15), $unit->sourceLines());
    }

    public function testCannotBeCreatedForInternalFunction(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forFunction('abs');
    }
}
