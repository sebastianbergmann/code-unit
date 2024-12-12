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
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeUnit\Fixture\FixtureClass;
use SebastianBergmann\CodeUnit\Fixture\FixtureInterface;
use SebastianBergmann\CodeUnit\Fixture\FixtureTrait;

#[CoversClass(ClassUnit::class)]
#[CoversClass(CodeUnit::class)]
#[UsesClass(CodeUnitCollection::class)]
#[UsesClass(CodeUnitCollectionIterator::class)]
#[UsesClass(Mapper::class)]
#[TestDox('ClassUnit')]
#[Small]
final class ClassUnitTest extends TestCase
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
        $this->assertFalse($unit->isFile());

        $this->assertSame(FixtureClass::class, $unit->name());
        $this->assertSame(realpath(__DIR__ . '/../_fixture/FixtureClass.php'), $unit->sourceFileName());
        $this->assertSame(range(12, 28), $unit->sourceLines());
    }

    public function testCannotBeCreatedForInternalClass(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forClass(Exception::class);
    }

    public function testCannotBeCreatedForInterface(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forClass(FixtureInterface::class);
    }

    public function testCannotBeCreatedForTrait(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forClass(FixtureTrait::class);
    }
}
