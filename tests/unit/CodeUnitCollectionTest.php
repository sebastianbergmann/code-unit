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
 * @covers \SebastianBergmann\CodeUnit\CodeUnitCollection
 * @covers \SebastianBergmann\CodeUnit\CodeUnitCollectionIterator
 *
 * @uses \SebastianBergmann\CodeUnit\CodeUnit
 *
 * @testdox CodeUnitCollection
 */
final class CodeUnitCollectionTest extends TestCase
{
    /**
     * @var InterfaceUnit
     */
    private $interface;

    /**
     * @var ClassUnit
     */
    private $class;

    protected function setUp(): void
    {
        $this->interface = CodeUnit::forInterface(FixtureInterface::class);
        $this->class     = CodeUnit::forClass(FixtureClass::class);
    }

    /**
     * @testdox Can be created from array of CodeUnit objects
     */
    public function testCanBeCreatedFromArrayOfObjects(): void
    {
        $collection = CodeUnitCollection::fromArray([$this->interface, $this->class]);

        $this->assertSame([$this->interface, $this->class], $collection->asArray());
    }

    /**
     * @testdox Can be created from list of CodeUnit objects
     */
    public function testCanBeCreatedFromListOfObjects(): void
    {
        $collection = CodeUnitCollection::fromList($this->interface, $this->class);

        $this->assertSame([$this->interface, $this->class], $collection->asArray());
    }

    /**
     * @testdox Can be created from 'function_name' string
     */
    public function testCanBeCreatedFromStringWithFunctionName(): void
    {
        $units = CodeUnitCollection::fromString('SebastianBergmann\CodeUnit\Fixture\f');

        $this->assertSame('SebastianBergmann\CodeUnit\Fixture\f', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can be created from 'ClassName' string
     */
    public function testCanBeCreatedFromStringWithClassName(): void
    {
        $units = CodeUnitCollection::fromString(FixtureClass::class);

        $this->assertSame(FixtureClass::class, $units->asArray()[0]->name());
    }

    /**
     * @testdox Can be created from 'ClassName::methodName' string
     */
    public function testCanBeCreatedFromStringWithClassNameAndMethodName(): void
    {
        $units = CodeUnitCollection::fromString(FixtureClass::class . '::publicMethod');

        $this->assertSame(FixtureClass::class . '::publicMethod', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can be created from 'ClassName::<public>' string
     */
    public function testCanBeCreatedFromStringWithClassNameAndPublicMethodSelector(): void
    {
        $units = CodeUnitCollection::fromString(FixtureClass::class . '::<public>');

        $this->assertCount(1, $units);
        $this->assertSame(FixtureClass::class . '::publicMethod', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can be created from 'ClassName::<!public>' string
     */
    public function testCanBeCreatedFromStringWithClassNameAndNoPublicMethodSelector(): void
    {
        $units = CodeUnitCollection::fromString(FixtureClass::class . '::<!public>');

        $this->assertCount(2, $units);
        $this->assertSame(FixtureClass::class . '::protectedMethod', $units->asArray()[0]->name());
        $this->assertSame(FixtureClass::class . '::privateMethod', $units->asArray()[1]->name());
    }

    /**
     * @testdox Can be created from 'ClassName::<protected>' string
     */
    public function testCanBeCreatedFromStringWithClassNameAndProtectedMethodSelector(): void
    {
        $units = CodeUnitCollection::fromString(FixtureClass::class . '::<protected>');

        $this->assertCount(1, $units);
        $this->assertSame(FixtureClass::class . '::protectedMethod', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can be created from 'ClassName::<!protected>' string
     */
    public function testCanBeCreatedFromStringWithClassNameAndNoProtectedMethodSelector(): void
    {
        $units = CodeUnitCollection::fromString(FixtureClass::class . '::<!protected>');

        $this->assertCount(2, $units);
        $this->assertSame(FixtureClass::class . '::publicMethod', $units->asArray()[0]->name());
        $this->assertSame(FixtureClass::class . '::privateMethod', $units->asArray()[1]->name());
    }

    /**
     * @testdox Can be created from 'ClassName::<private>' string
     */
    public function testCanBeCreatedFromStringWithClassNameAndPrivateMethodSelector(): void
    {
        $units = CodeUnitCollection::fromString(FixtureClass::class . '::<private>');

        $this->assertCount(1, $units);
        $this->assertSame(FixtureClass::class . '::privateMethod', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can be created from 'ClassName::<!private>' string
     */
    public function testCanBeCreatedFromStringWithClassNameAndNoPrivateMethodSelector(): void
    {
        $units = CodeUnitCollection::fromString(FixtureClass::class . '::<!private>');

        $this->assertCount(2, $units);
        $this->assertSame(FixtureClass::class . '::publicMethod', $units->asArray()[0]->name());
        $this->assertSame(FixtureClass::class . '::protectedMethod', $units->asArray()[1]->name());
    }

    /**
     * @testdox Can be created from 'InterfaceName' string
     */
    public function testCanBeCreatedFromStringWithInterfaceName(): void
    {
        $units = CodeUnitCollection::fromString(FixtureInterface::class);

        $this->assertSame(FixtureInterface::class, $units->asArray()[0]->name());
    }

    /**
     * @testdox Can be created from 'InterfaceName::methodName' string
     */
    public function testCanBeCreatedFromStringWithInterfaceNameAndMethodName(): void
    {
        $units = CodeUnitCollection::fromString(FixtureInterface::class . '::method');

        $this->assertSame(FixtureInterface::class . '::method', $units->asArray()[0]->name());
    }

    /**
     * @testdox Can be created from 'TraitName' string
     */
    public function testCanBeCreatedFromStringWithTraitName(): void
    {
        $units = CodeUnitCollection::fromString(FixtureTrait::class);

        $this->assertSame(FixtureTrait::class, $units->asArray()[0]->name());
    }

    /**
     * @testdox Can be created from 'TraitName::methodName' string
     */
    public function testCanBeCreatedFromStringWithTraitNameAndMethodName(): void
    {
        $units = CodeUnitCollection::fromString(FixtureTrait::class . '::method');

        $this->assertSame(FixtureTrait::class . '::method', $units->asArray()[0]->name());
    }

    public function testCannotBeCreatedFromInvalidString(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnitCollection::fromString('invalid');
    }

    public function testCanBeCounted(): void
    {
        $collection = CodeUnitCollection::fromList($this->interface, $this->class);

        $this->assertCount(2, $collection);
        $this->assertFalse($collection->isEmpty());
    }

    public function testCanBeIterated(): void
    {
        foreach (CodeUnitCollection::fromList($this->interface) as $key => $value) {
            $this->assertSame(0, $key);
            $this->assertSame($this->interface, $value);
        }
    }

    public function testSourceLinesCanBeRetrieved(): void
    {
        $collection = CodeUnitCollection::fromList(
            $this->interface,
            $this->class,
            CodeUnit::forClass(FixtureClass::class)
        );

        $this->assertSame(
            [
                \realpath(__DIR__ . '/../_fixture/FixtureClass.php')     => \range(12, 28),
                \realpath(__DIR__ . '/../_fixture/FixtureInterface.php') => \range(12, 15),
            ],
            $collection->sourceLines()
        );
    }
}
