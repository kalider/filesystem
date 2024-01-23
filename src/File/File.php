<?php

namespace Kalider\Filesystem\File;

use Kalider\Filesystem\File\Traits\FileTraits;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class File extends SymfonyFile
{
    use FileTraits;
}
