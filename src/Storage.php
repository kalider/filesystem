<?php

namespace Kalider\Libs;

use League\Flysystem\Filesystem;
use Kalider\Libs\DriverResolver\BaseDriverResolver;
use Kalider\Libs\DriverResolver\LocalDriverResolver;
use Kalider\Libs\DriverResolver\S3DriverResolver;
use Kalider\Libs\Exception\DiskNotFoundException;

class Storage
{

    protected static $disks = [];
    protected static $defaultDisk = null;

    protected static $hasInitResolvers = false;
    protected static $driverResolvers = [];
    protected static $configs;

    public static function init(array $configs)
    {
        static::initDriverResolvers();

        if (!isset($configs['default'])) {
            throw new MissingRequiredParameterException("Missing required parameter 'default' in configuration");
        }

        static::$defaultDisk = $configs['default'];
        
        if (!isset($configs['disks'])) {
            throw new MissingRequiredParameterException("Missing required parameter 'disks' in configuration");
        }

        foreach ($configs['disks'] as $key => $disk) {
            if (!isset($disk['driver'])) {
                throw new MissingRequiredParameterException("Missing required parameter 'driver' in {$key} configuration");
            }

            static::$disks[$key] = static::buildDisk($disk['driver'], $disk);
        }

        static::$configs = $configs;
    }

    public static function buildDisk($driver, array $config)
    {
        $driver = $config['driver'];
        if (!isset(static::$driverResolvers[$driver])) {
            throw new \Exception("Driver '{$driver}' is not registered");
        }

        $resolver = static::$driverResolvers[$driver];
        return $resolver->makeFilesystem($config);
    }

    public static function registerDriver($key, BaseDriverResolver $resolver)
    {
        static::$driverResolvers[$key] = $resolver;
    }

    protected static function initDriverResolvers()
    {
        if (!static::$hasInitResolvers) {
            static::registerDriver('local', new LocalDriverResolver());
            static::registerDriver('s3', new S3DriverResolver());

            static::$hasInitResolvers = true;
        }
    }

    public static function disk(string $name = null): Filesystem {
        $name = $name ?? static::$defaultDisk;

        if (!isset(static::$disks[$name])) {
            throw new DiskNotFoundException("Disk '{$name}' not found.");
        }

        return static::$disks[$name];
    }

    public static function url(string $pathfile, string $disk = null) : string {
        $disk = $disk ?? static::$defaultDisk;
        
        if (!isset(static::$configs['disks'][$disk])) {
            throw new DiskNotFoundException("Disk '{$disk}' not found.");
        }

        if (!isset(static::$configs['disks'][$disk]['url'])) {
            throw new \InvalidArgumentException("Disk doesn't have 'url' param");
        }

        return rtrim(static::$configs['disks'][$disk]['url'], '/') . '/' . ltrim($pathfile, '/');
    }
}
