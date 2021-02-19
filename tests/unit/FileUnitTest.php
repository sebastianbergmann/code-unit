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
final class FileUnitTest extends TestCase
{
    public function testCanBeCreatedFromAbsoluteFileName(): void
    {
        $file = realpath(__FILE__);
        $unit = CodeUnit::forAbsoluteFile($file);

        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());
        $this->assertTrue($unit->isFile());

        $this->assertSame('file:' . $file, $unit->name());
        $this->assertSame(realpath($file), $unit->sourceFileName());
        $this->assertSame(range(1, 53), $unit->sourceLines());
    }

    public function testCannotBeCreatedForNonExistentFile(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forAbsoluteFile(__DIR__ . '/FileUnitTest2.php');
    }
}
