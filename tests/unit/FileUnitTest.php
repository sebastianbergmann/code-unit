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

use function chmod;
use function file_exists;
use function is_readable;
use function range;
use function realpath;
use function sys_get_temp_dir;
use function tempnam;
use function touch;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodeUnit::class)]
#[CoversClass(FileUnit::class)]
#[UsesClass(CodeUnitCollection::class)]
#[UsesClass(CodeUnitCollectionIterator::class)]
#[UsesClass(Mapper::class)]
#[TestDox('FileUnit')]
final class FileUnitTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->temporaryFiles as $temporaryFile) {
            unlink($temporaryFile);
        }
        $this->temporaryFiles = [];
    }

    public function testCanBeCreatedFromAbsoluteFileName(): void
    {
        $file = realpath(__FILE__);
        $unit = CodeUnit::forFileWithAbsolutePath($file);

        $this->assertFalse($unit->isClass());
        $this->assertFalse($unit->isClassMethod());
        $this->assertFalse($unit->isInterface());
        $this->assertFalse($unit->isInterfaceMethod());
        $this->assertFalse($unit->isTrait());
        $this->assertFalse($unit->isTraitMethod());
        $this->assertFalse($unit->isFunction());
        $this->assertTrue($unit->isFile());

        $this->assertSame($file, $unit->name());
        $this->assertSame(realpath($file), $unit->sourceFileName());
        $this->assertSame(range(1, 85), $unit->sourceLines());
    }

    public function testCannotBeCreatedForNonExistentFile(): void
    {
        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forFileWithAbsolutePath(__DIR__ . '/FileUnitTest2.php');
    }

    public function testCannotBeCreatedForUnreadableFile(): void
    {
        $this->temporaryFiles[] = $fileName = tempnam(sys_get_temp_dir(), 'fileunit');

        $this->assertTrue(touch($fileName));
        $this->assertTrue(chmod($fileName, 0000));
        $this->assertTrue(file_exists($fileName));
        $this->assertFalse(is_readable($fileName));

        $this->expectException(InvalidCodeUnitException::class);

        CodeUnit::forFileWithAbsolutePath($fileName);
    }
}
