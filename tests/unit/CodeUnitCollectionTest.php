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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeUnit\Fixture\FixtureClass;
use SebastianBergmann\CodeUnit\Fixture\FixtureInterface;

#[CoversClass(CodeUnitCollection::class)]
#[CoversClass(CodeUnitCollectionIterator::class)]
#[UsesClass(CodeUnit::class)]
#[UsesClass(Mapper::class)]
#[TestDox('CodeUnitCollection')]
#[Small]
final class CodeUnitCollectionTest extends TestCase
{
    private InterfaceUnit $interface;
    private ClassUnit $class;

    protected function setUp(): void
    {
        $this->interface = CodeUnit::forInterface(FixtureInterface::class);
        $this->class     = CodeUnit::forClass(FixtureClass::class);
    }

    #[TestDox('Can be created from list of CodeUnit objects')]
    public function testCanBeCreatedFromListOfObjects(): void
    {
        $collection = CodeUnitCollection::fromList($this->interface, $this->class);

        $this->assertSame([$this->interface, $this->class], $collection->asArray());
    }

    public function testCanBeCounted(): void
    {
        $collection = CodeUnitCollection::fromList($this->interface, $this->class);

        $this->assertCount(2, $collection);
        $this->assertFalse($collection->isEmpty());
    }

    public function testCanBeIterated(): void
    {
        $array = [];

        foreach (CodeUnitCollection::fromList($this->interface, $this->class) as $key => $value) {
            $array[$key] = $value;
        }

        $this->assertCount(2, $array);

        $this->assertArrayHasKey(0, $array);
        $this->assertSame($this->interface, $array[0]);

        $this->assertArrayHasKey(1, $array);
        $this->assertSame($this->class, $array[1]);
    }

    public function testCanBeMergedWithAnotherCollectionOfCodeUnitObjects(): void
    {
        $this->assertSame(
            [
                $this->class,
                $this->interface,
            ],
            CodeUnitCollection::fromList($this->class)->mergeWith(
                CodeUnitCollection::fromList($this->interface),
            )->asArray(),
        );
    }
}
