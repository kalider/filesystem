<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Kalider\Filesystem\File\UploadedFile;
use Kalider\Filesystem\Storage;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;

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
            'size' => filesize(__DIR__ . '/../storage/assets/foo.txt'),
            'error' => UPLOAD_ERR_OK
        );

        $_FILES['image1'] = array(
            'name' => 'image1.jpeg',
            'tmp_name' => __DIR__ . '/../storage/assets/image1.jpeg',
            'type' => 'image/jpeg',
            'size' => filesize(__DIR__ . '/../storage/assets/image1.jpeg'),
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
            ),
            'size' => array(
                filesize(__DIR__ . '/../storage/assets/foo.txt'),
                filesize(__DIR__ . '/../storage/assets/bar.txt')
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

    public function testGetInstanceByNameSingle(): void
    {
        $file = UploadedFile::getInstanceByName('single');

        $this->assertInstanceOf(UploadedFile::class, $file);
    }

    public function testGetInstanceByNameMultiple(): void
    {
        $files = UploadedFile::getInstanceByName('multiple');

        $this->assertIsArray($files);
        $this->assertInstanceOf(UploadedFile::class, $files[0]);
    }

    public function testValidation(): void
    {
        $file = UploadedFile::getInstanceByName('single', true);

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

    public function testValidationValid(): void
    {
        $file = UploadedFile::getInstanceByName('single', true);

        $violations = $file->validate([
            new Assert\NotBlank(),
            new Assert\File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'text/plain',
                ],
                'mimeTypesMessage' => 'Please upload a valid text file',
            ])
        ]);

        $this->assertInstanceOf(ConstraintViolationList::class, $violations);
        $this->assertEquals(0, count($violations));
    }

    public function testValidatorSuccess(): void
    {
        $violations = UploadedFile::validator([
            'single' => [
                new Assert\NotBlank(),
                new Assert\File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'text/plain',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid text file',
                ])
            ],
            'image1' => [
                new Assert\NotBlank(),
                new Assert\File([
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'image/jpeg',
                    ],
                ])
            ]
        ], true);

        $this->assertEquals(0, count($violations));
    }

    public function testValidatorFailed(): void
    {
        $violations = UploadedFile::validator([
            'single' => [
                new Assert\NotBlank(),
                new Assert\File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'text/csv',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid csv file.',
                ])
            ],
            'image1' => [
                new Assert\NotBlank(),
                new Assert\File([
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image png file.',
                ])
            ],
            'salah' => [new Assert\NotBlank(), new Assert\File()],
        ], true);

        $this->assertIsArray($violations);
        $this->assertEquals(3, count($violations));
        $this->assertEquals('Please upload a valid csv file.', $violations['single'][0]);
        $this->assertEquals('Please upload a valid image png file.', $violations['image1'][0]);
        $this->assertEquals('This value should not be blank.', $violations['salah'][0]);
    }

    public function testValidatorMultipleSuccess(): void
    {
        $violations = UploadedFile::validator([
            'multiple' => [
                new Assert\NotBlank(),
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid text file',
                    ])
                ])
            ],
        ], true);

        $this->assertEquals(0, count($violations));
    }

    public function testValidatorMultipleFailed(): void
    {
        $violations = UploadedFile::validator([
            'multiple' => [
                new Assert\NotBlank(),
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/csv',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid csv file',
                    ])
                ])
            ],
        ], true);

        $this->assertIsArray($violations);
        $this->assertEquals(2, count($violations['multiple']));
        $this->assertEquals('Please upload a valid csv file', $violations['multiple'][0][0]);
        $this->assertEquals('Please upload a valid csv file', $violations['multiple'][1][0]);
    }
}
