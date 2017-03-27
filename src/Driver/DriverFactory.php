<?php

namespace Cacko\ClamAv\Driver;

use Cacko\ClamAv\Exception\ConfigurationException;

/**
 * Class DriverFactory
 * @package Cacko\ClamAv\Driver
 */
class DriverFactory
{
    /**
     * Available drivers
     * @var array
     */


    const DRIVER_CLAMSCAN  = 'clamscan';
    const DRIVER_LOCAL = 'clamd_local';
    const DRIVER_REMOTE = 'clamd_remote';
    const DRIVER_DEFAULT = 'default';
    const DRIVER_DUMMY = 'dummy';

    const DRIVERS = [
        self::DRIVER_CLAMSCAN => ClamscanDriver::class,
        self::DRIVER_LOCAL => ClamdDriver::class,
        self::DRIVER_REMOTE => ClamdRemoteDriver::class,
        self::DRIVER_DEFAULT => ClamscanDriver::class,
        self::DRIVER_DUMMY => DummyDriver::class
    ];

    /**
     * @inheritdoc
     * @throws ConfigurationException
     */
    public static function create(array $config)
    {
        if (empty($config['driver'])) {
            throw new ConfigurationException('ClamAV driver required, please check your config.');
        }

        if (!array_key_exists($config['driver'], static::DRIVERS)) {
            throw new ConfigurationException(
                sprintf(
                    'Invalid driver "%s" specified. Available options are: %s',
                    $config['driver'],
                    join(', ', array_keys(static::DRIVERS))
                )
            );
        }

        $driver = static::DRIVERS[$config['driver']];
        unset($config['driver']);
        return new $driver($config);
    }
}
