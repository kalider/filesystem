<?php

namespace Kalider\Filesystem\DriverResolver;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class LocalDriverResolver extends BaseDriverResolver
{

    public function makeFilesystem(array $params)
    {
        $this->requireParams(['root'], $params);
        $root = $params['root'];

        return new Filesystem(new LocalFilesystemAdapter($root));
    }
}
