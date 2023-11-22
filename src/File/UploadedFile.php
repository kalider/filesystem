<?php

namespace Kalider\Libs\File;

use Kalider\Libs\File\Traits\FileTraits;
use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFileSymfony;

class UploadedFile extends UploadedFileSymfony {
    use FileTraits;
}