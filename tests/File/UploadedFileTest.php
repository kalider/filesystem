<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Kalider\Libs\File\UploadedFile;
use Kalider\Libs\Storage;

final class UploadedFileTest extends TestCase
{
    protected function setUp(): void
    {
        Storage::init([
            'default' => 'public',
            'disks' => [
                'public' => [
                    'driver' => 'local',
                    'root' => './tests/storage/public'
                ]
            ]
        ]);

        $_FILES['single'] = array(
            'name' => 'foo.txt',
            'tmp_name' => __DIR__ . '/../storage/assets/foo.txt',
            'type' => 'text/plain',
            'error' => UPLOAD_ERR_OK
        );

        $_FILES['multiple'] = array(
            'name' => array(
                'foo.txt',
                'bar.txt'
            ),
            'tmp_name' => array(
               __DIR__ . '/../storage/assets/foo.txt',
               __DIR__ . '/../storage/assets/bar.txt'
            ),
            'error' => array(
                UPLOAD_ERR_OK,
                UPLOAD_ERR_OK
            ),
            'type' => array(
                'text/plain',
                'text/plain'
            )
        );
    }

    public function testStore(): void
    {
        $file = new UploadedFile($_FILES['single']['tmp_name'], $_FILES['single']['name']);

        $path = $file->store('uploaded');

        $this->assertEquals($file->hashName('uploaded'), $path);
        $this->assertTrue(Storage::disk()->fileExists($path));
    }

    public function testStoreAs(): void
    {
        $file = new UploadedFile($_FILES['single']['tmp_name'], $_FILES['single']['name']);

        $path = $file->storeAs('uploaded', 'store-as');

        $this->assertEquals('uploaded/store-as.' . $file->guessExtension(), $path);
        $this->assertTrue(Storage::disk()->fileExists($path));
    }

    public function testGetInstanceByNameSingle() : void {
        $file = UploadedFile::getInstanceByName('single');

        $this->assertInstanceOf(UploadedFile::class, $file);
    }
    
    public function testGetInstanceByNameMultiple() : void {
        $files = UploadedFile::getInstanceByName('multiple');

        $this->assertIsArray($files);
        $this->assertInstanceOf(UploadedFile::class, $files[0]);
    }
}
