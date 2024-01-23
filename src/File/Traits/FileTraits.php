<?php

namespace Kalider\Filesystem\File\Traits;

use Kalider\Filesystem\Storage;
use Symfony\Component\Validator\Validation;

trait FileTraits
{
    /**
     * The cache copy of the file's hash name.
     *
     * @var string
     */
    protected $hashName = null;

    /**
     * Get a filename for the file.
     *
     * @param  string|null  $path
     * @return string
     */
    public function hashName($path = null)
    {
        if ($path) {
            $path = rtrim($path, '/') . '/';
        }

        $hash = $this->hashName ?: $this->hashName = $this->genereateHash(40);

        if ($extension = $this->guessExtension()) {
            $extension = '.' . $extension;
        }

        return $path . $hash . $extension;
    }

    public function genereateHash($length = 16): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytesSize = (int) ceil($size / 3) * 3;

            $bytes = random_bytes($bytesSize);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    public function store(string $path, string $disk = null) : string {
        $filename = $this->hashName($path);

        Storage::disk($disk)->write($filename, $this->getContent());
        
        return $filename;
    }
    
    public function storeAs(string $path, string $name, string $disk = null) : string {
        if ($path) {
            $path = rtrim($path, '/') . '/';
        }

        if ($extension = $this->guessExtension()) {
            $extension = '.' . $extension;
        }

        $filename = $path . $name . $extension;
        Storage::disk($disk)->write($filename, $this->getContent());
        
        return $filename;
    }

    public function validate($constraints)
    {
        $validator = Validation::createValidator();

        return $validator->validate($this, $constraints);
    }
}
