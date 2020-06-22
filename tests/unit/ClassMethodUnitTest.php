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
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeUnit\Fixture\FixtureClass;
use SebastianBergmann\CodeUnit\Fixture\FixtureInterface;
use SebastianBergmann\CodeUnit\Fixture\FixtureTrait;

/**
 * @covers \SebastianBergmann\CodeUnit\ClassMethodUnit
 * @covers \SebastianBergmann\CodeUnit\CodeUnit
 *
 * @uses \SebastianBergmann\CodeUnit\CodeUnitCollection
 * @uses \SebastianBergmann\CodeUnit\CodeUnitCollectionIterator
 * @uses \SebastianBergmann\CodeUnit\Mapper
 *
 * @testdox ClassMethodUnit
 */
final class ClassMethodUnitTest extends TestCase
{
    public function testCanBeCreatedFromNameOfUserDefinedClassAndMethodName(): void
    {
        $unit = CodeUnit::forClassMethod(FixtureClass::class, 'publicMethod');

        $this->assertFalse($unit->isClass());
        $this->assertTrue($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureClass::class . '::publicMethod', $unit->name());
        $this->assertSame(realpath(__DIR__ . '/../_fixture/FixtureClass.php'), $unit->sourceFileName());
        $this->assertSame(range(14, 17), $unit->sourceLines());
    }

    public function testCannotBeCreatedForMethodOfInternalClass(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forClassMethod(Exception::class, 'getMessage');
    }

    public function testCannotBeCreatedForInterfaceMethod(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forClassMethod(FixtureInterface::class, 'method');
    }

    public function testCannotBeCreatedForTraitMethod(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forClassMethod(FixtureTrait::class, 'method');
    }
}
