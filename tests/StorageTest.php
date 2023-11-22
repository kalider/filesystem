<?php

declare(strict_types=1);

use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Kalider\Libs\Exception\DiskNotFoundException;
use Kalider\Libs\File\File;
use Kalider\Libs\Storage;

final class StorageTest extends TestCase
{
    protected function setUp(): void
    {
        Storage::init([
            'default' => 'public',
            'disks' => [
                'public' => [
                    'driver' => 'local',
                    'root' => './tests/storage/public',
                    'url' => 'http://localhost'
                ],
                'private' => [
                    'driver' => 'local',
                    'root' => './tests/storage/private',
                ]
            ]
        ]);
    }

    public function testInitStorage(): void
    {
        $this->assertInstanceOf(Filesystem::class, Storage::disk('public'));
    }

    public function testDefaultDisk() : void {
        $this->assertInstanceOf(Filesystem::class, Storage::disk());
    }
    
    public function testDefaultDiskNotFound() : void {
        Storage::init([
            'default' => 'local',
            'disks' => [
                'public' => [
                    'driver' => 'local',
                    'root' => './tests/storage/public'
                ]
            ]
        ]);

        $this->expectException(DiskNotFoundException::class);

        Storage::disk();
    }

    public function testGetUrl() : void {
        $file = new File(__DIR__ . '/storage/assets/bar.txt');

        $path = $file->store('file');

        $this->assertTrue(Storage::disk()->fileExists($path));
        $this->assertEquals('http://localhost/' . $path, Storage::url($path));
    }
    
    public function testGetUrlInPrivateDisk() : void {
        $file = new File(__DIR__ . '/storage/assets/bar.txt');

        $path = $file->store('file', 'private');

        $this->assertTrue(Storage::disk('private')->fileExists($path));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Disk doesn't have 'url' param");

        $url = Storage::url($path, 'private');
    }
    
    public function testDeleteFile() : void {
        $file = new File(__DIR__ . '/storage/assets/bar.txt');

        $path = $file->store('deleted');

        $this->assertTrue(Storage::disk()->fileExists($path));

        Storage::disk()->delete($path);
        $this->assertFalse(Storage::disk()->fileExists($path));
    }
}
