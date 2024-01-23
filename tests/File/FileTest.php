<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Kalider\Filesystem\File\File;
use Kalider\Filesystem\Storage;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;

final class FileTest extends TestCase
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
    }

    public function testStore(): void
    {
        $file = new File(__DIR__ . '/../storage/assets/bar.txt');

        $path = $file->store('file');

        $this->assertEquals($file->hashName('file'), $path);
        $this->assertTrue(Storage::disk()->fileExists($path));
    }

    public function testStoreAs(): void
    {
        $file = new File(__DIR__ . '/../storage/assets/bar.txt');

        $path = $file->storeAs('file', 'store-as');

        $this->assertEquals('file/store-as.' . $file->guessExtension(), $path);
        $this->assertTrue(Storage::disk()->fileExists($path));
    }

    public function testValidation(): void
    {
        $file = new File(__DIR__ . '/../storage/assets/bar.txt');

        $violations = $file->validate([
            new Assert\NotBlank(),
            new Assert\File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'application/pdf',
                    'application/x-pdf',
                ],
                'mimeTypesMessage' => 'Please upload a valid PDF',
            ])
        ]);

        $this->assertInstanceOf(ConstraintViolationList::class, $violations);
        $this->assertEquals('Please upload a valid PDF', $violations[0]->getMessage());
    }
}
