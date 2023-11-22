<?php

namespace Kalider\Libs;

use Kalider\Libs\File\UploadedFile;

class Request
{
    public static function file(string $key): UploadedFile
    {
        if (isset($_FILES[$key]) === false) {
            throw new \InvalidArgumentException("Cannot find uploaded file(s) identified by key: $key");
        }

        return new UploadedFile($_FILES[$key]['tmp_name'], $_FILES[$key]['name'], $_FILES[$key]['type'], $_FILES[$key]['error']);
    }

    public static function files(string $key): array
    {
        if (isset($_FILES[$key]) === false) {
            throw new \InvalidArgumentException("Cannot find uploaded file(s) identified by key: $key");
        }

        if (!is_array($_FILES[$key]['tmp_name'])) {
            throw new \Exception("Uploaded file is not multiple");
        }

        $files = [];
        foreach ($_FILES[$key]['tmp_name'] as $index => $tmp_name) {
            $files[] = new UploadedFile(
                $tmp_name, 
                $_FILES[$key]['name'][$index], 
                $_FILES[$key]['type'][$index], 
                $_FILES[$key]['error'][$index]
            );
        }

        return $files;
    }
}
