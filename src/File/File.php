<?php

namespace Kalider\Libs\File;

use Kalider\Libs\File\Traits\FileTraits;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class File extends SymfonyFile
{
    use FileTraits;
}
