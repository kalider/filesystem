<?php

namespace Kalider\Filesystem\File;

use Kalider\Filesystem\File\Traits\FileTraits;
use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFileSymfony;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class UploadedFile extends UploadedFileSymfony
{
    use FileTraits;

    /**
     * Retrieve a file from the request.
     *
     * @param  string  $key
     * @return \Kalider\Filesystem\File\UploadedFile|\Kalider\Filesystem\File\UploadedFile[]|array
     */

    public static function getInstanceByName(string $key, bool $test = false)
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
                    $_FILES[$key]['error'][$index],
                    $test
                );
            }

            return $files;
        }

        return new static($_FILES[$key]['tmp_name'], $_FILES[$key]['name'], $_FILES[$key]['type'], $_FILES[$key]['error'], $test);
    }

    public static function validator($constraints, bool $test = false)
    {
        $validator = Validation::createValidator();

        $inputs = [];
        foreach (array_keys($constraints) as $key) {
            if (isset($_FILES[$key]) === false) {
                $inputs[$key] = null;
                continue;
            }

            $inputs[$key] = self::getInstanceByName($key, $test);
        }

        $violations = $validator->validate($inputs, new Assert\Collection($constraints));

        if (count($violations) == 0) {
            return [];
        }

        $errors = [];
        foreach ($violations as $violation) {
            $field = ltrim($violation->getPropertyPath(), "[");
            $field = rtrim($field, "]");
            $fields = explode('][', $field);

            $data = [$violation->getMessage()];
            foreach (array_reverse($fields) as $i => $fName) {
                if (count($fields) == ($i + 1)) {
                    $errors[$fName] = isset($errors[$fName]) ? array_merge($errors[$fName], $data) : $data;
                    break;
                }

                $data = $i == 0 ? [$fName => [$violation->getMessage()]] : [$fName => $data];
            }
        }

        return $errors;
    }
}
