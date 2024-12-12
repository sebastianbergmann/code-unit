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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodeUnit::class)]
#[CoversClass(FunctionUnit::class)]
#[UsesClass(CodeUnitCollection::class)]
#[UsesClass(CodeUnitCollectionIterator::class)]
#[UsesClass(Mapper::class)]
#[TestDox('FunctionUnit')]
#[Small]
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
        $this->assertSame(realpath(__DIR__ . '/../_fixture/function.php'), $unit->sourceFileName());
        $this->assertSame(range(11, 14), $unit->sourceLines());
    }

    public function testCannotBeCreatedForInternalFunction(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forFunction('abs');
    }
}
