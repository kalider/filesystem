<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Kalider\Libs\File\UploadedFile;
use Kalider\Libs\Request;

final class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_FILES['multiple'] = array(
            'name' => array(
                'foo.txt',
                'bar.txt'
            ),
            'tmp_name' => array(
               __DIR__ . '/storage/assets/foo.txt',
               __DIR__ . '/storage/assets/bar.txt'
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

        $_FILES['single'] = array(
            'name' => 'foo.txt',
            'tmp_name' => __DIR__ . '/storage/assets/foo.txt',
            'type' => 'text/plain',
            'error' => UPLOAD_ERR_OK
        );
    }

    public function testRequestFile() : void {
        $file = Request::file('single');

        $this->assertInstanceOf(UploadedFile::class, $file);
    }
    
    public function testRequestFiles() : void {
        $files = Request::files('multiple');

        $this->assertIsArray($files);
        $this->assertInstanceOf(UploadedFile::class, $files[0]);
    }
}
