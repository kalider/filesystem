<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Kalider\Libs\File\File;
use Kalider\Libs\Storage;

final class FileTest extends TestCase {
    protected function setUp() : void {
        Storage::init([
            'default' => 'public',
            'disks' => [
                'public' => [
                    'driver' => 'local',
                    'root' => './tests/storage/public'
                ]
            ]
        ]);
    }
    public function testStore() : void {
        $file = new File(__DIR__ . '/../storage/assets/bar.txt');

        $path = $file->store('file');

        $this->assertEquals($file->hashName('file'), $path);
        $this->assertTrue(Storage::disk()->fileExists($path));
    }
    
    public function testStoreAs() : void {
        $file = new File(__DIR__ . '/../storage/assets/bar.txt');

        $path = $file->storeAs('file', 'store-as');

        $this->assertEquals('file/store-as.' . $file->guessExtension(), $path);
        $this->assertTrue(Storage::disk()->fileExists($path));
    }
}