<?php

namespace Kalider\Libs\DriverResolver;

use Aws\Credentials\CredentialProvider;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

class S3DriverResolver extends BaseDriverResolver
{

    public function makeFilesystem(array $params)
    {
        $this->requireParams(['secret', 'key', 'version', 'bucket', 'url', 'region', 'prefix'], $params);
        
        $params += [
            'version' => 'latest',
        ];

        if (! empty($params['token'])) {
            putenv("AWS_SESSION_TOKEN={$params['token']}");
        }

        if (! empty($params['key']) && ! empty($params['secret'])) {
            putenv("AWS_ACCESS_KEY_ID={$params['key']}");
            putenv("AWS_SECRET_ACCESS_KEY={$params['secret']}");
            
            $params['credentials'] = CredentialProvider::env();
        }

        $bucket = $params['bucket'];
        $prefix = $params['prefix'];

        $params = $this->except($params, ['token', 'url', 'prefix', 'bucket']);

        $client = new S3Client($params);

        $adapter = new AwsS3V3Adapter(
            // S3Client
            $client,
            // Bucket name
            $bucket,
            $prefix
        );

        return new Filesystem($adapter);
    }

    protected function except($array, $keys) {
        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (in_array($key, array_keys($array))) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
