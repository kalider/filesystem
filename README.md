# Filesystem

## Instalasi

```bash
composer require kalider/filesystem
```

## Konfigurasi dan Inisialisasi

```php
use Kalider\Filesystem\Storage;

Storage::init([
    'default' => 'public',
    'disks' => [
        // local
        'public' => [
            'driver' => 'local',
            'root' => './storages/public',
            'url' => 'http://localhost'
        ],

        // local private
        'private' => [
            'driver' => 'local',
            'root' => './storages/private',
        ],

        // s3 fybe
        's3' => [
            'driver' => 's3',
            'key' => 'STORAGE_S3_KEY',
            'secret' => 'STORAGE_S3_SECRET',
            'version' => 'latest',
            'bucket' => 'STORAGE_S3_BUCKET',
            'prefix' => 'STORAGE_S3_PREFIX',
            'region' => 'STORAGE_S3_REGION',
            'endpoint' => 'STORAGE_S3_ENDPOINT',
            'bucket_endpoint' => true,
            'url' => 'STORAGE_S3_URL'
        ]
    ]
]);
```

## File & Uploaded File

### Instance

```php
use Kalider\Filesystem\File\File;
use Kalider\Filesystem\File\UploadedFile;

// file
$file = new File('path/to/file');

// uploaded file
$uploaded = UploadedFile::getInstanceByName('name');
```

### Store

Simpan file ke storage yang diinginkan, nama file digenerate otomatis

```php
use Kalider\Filesystem\File\File;
use Kalider\Filesystem\File\UploadedFile;

// file
$file = new File('path/to/file');
$path = $file->store('path/to/store/file');

// uploaded file
$uploaded = UploadedFile::getInstanceByName('name');
$path = $uploaded->store('path/to/store/file');
```

Simpan ke storage selain default

```php
$path = $file->store('path/to/store/file', 's3');
```

### Store As

Simpan file ke storage dengan mengganti nama

```php
use Kalider\Filesystem\File\File;
use Kalider\Filesystem\File\UploadedFile;

// file
$file = new File('path/to/file');
$path = $file->storeAs('path/to/store/file', 'file-name');

// uploaded file
$uploaded = UploadedFile::getInstanceByName('name');
$path = $uploaded->storeAs('path/to/store/file', 'file-name');
```

## Validasi

Validasi menggunakan [Symfony Validator](https://symfony.com/doc/5.x/reference/constraints.html)

1. Validasi per instance, mengembalikan `Symfony\Component\Validator\ConstraintViolationList`
```php
use Kalider\Filesystem\File\File;
use Kalider\Filesystem\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

// file
$file = new File('path/to/file');
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

// uploaded file
$uploaded = UploadedFile::getInstanceByName('name');
$violations = $uploaded->validate([
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
```

2. Validasi bulk uploaded file

```php
use Kalider\Filesystem\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

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
]);

if (count($violations) > 0) {
    // invalid 
    // errors tersimpan pada var $violations
}

// valid
```