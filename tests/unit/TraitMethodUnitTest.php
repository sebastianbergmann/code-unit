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
use SebastianBergmann\CodeUnit\Fixture\FixtureClass;
use SebastianBergmann\CodeUnit\Fixture\FixtureInterface;
use SebastianBergmann\CodeUnit\Fixture\FixtureTrait;

/**
 * @covers \SebastianBergmann\CodeUnit\TraitMethodUnit
 * @covers \SebastianBergmann\CodeUnit\CodeUnit
 *
 * @uses \SebastianBergmann\CodeUnit\CodeUnitCollection
 * @uses \SebastianBergmann\CodeUnit\CodeUnitCollectionIterator
 * @uses \SebastianBergmann\CodeUnit\Mapper
 *
 * @testdox TraitMethodUnit
 */
final class TraitMethodUnitTest extends TestCase
{
    public function testCanBeCreatedFromNameOfUserDefinedTraitAndMethodName(): void
    {
        $unit = CodeUnit::forTraitMethod(FixtureTrait::class, 'method');

        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertTrue($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());

        $this->assertSame(FixtureTrait::class . '::method', $unit->name());
        $this->assertSame(realpath(__DIR__ . '/../_fixture/FixtureTrait.php'), $unit->sourceFileName());
        $this->assertSame(range(14, 17), $unit->sourceLines());
    }

    public function testCannotBeCreatedForClassMethod(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forTraitMethod(FixtureClass::class, 'publicMethod');
    }

    public function testCannotBeCreatedForInterfaceMethod(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forTraitMethod(FixtureInterface::class, 'method');
    }
}
