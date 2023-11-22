<?php

namespace Kalider\Libs\File;

use Kalider\Libs\File\Traits\FileTraits;
use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFileSymfony;

class UploadedFile extends UploadedFileSymfony
{
    use FileTraits;

    /**
     * Retrieve a file from the request.
     *
     * @param  string  $key
     * @return \Kalider\Libs\File\UploadedFile|\Kalider\Libs\File\UploadedFile[]|array
     */

    public static function getInstanceByName(string $key)
    {
        if (isset($_FILES[$key]) === false) {
            throw new \InvalidArgumentException("Cannot find uploaded file(s) identified by key: $key");
        }

        if (is_array($_FILES[$key]['tmp_name'])) {
            $files = [];
            foreach ($_FILES[$key]['tmp_name'] as $index => $tmp_name) {
                $files[] = new static(
                    $tmp_name,
                    $_FILES[$key]['name'][$index],
                    $_FILES[$key]['type'][$index],
                    $_FILES[$key]['error'][$index]
                );
            }

            return $files;
        }

        return new static($_FILES[$key]['tmp_name'], $_FILES[$key]['name'], $_FILES[$key]['type'], $_FILES[$key]['error']);
    }
}
