<?php

namespace Cacko\ClamAv\Socket;

use Cacko\ClamAv\Exception\ConfigurationException;

/**
 * Class SocketFactory
 * @package Cacko\ClamAv\Socket
 */
class SocketFactory
{
    /**
     * Create socket
     * @param $options
     * @return SocketInterface
     * @throws ConfigurationException
     */
    public static function create($options)
    {
        if (empty($options['socket']) && empty($options['host'])) {
            throw new ConfigurationException(
                'Socket requires host IP address or socket path, please check your config.'
            );
        }

        if (!empty($options['host'])) {
            return new Socket([
                    'host' => $options['host'],
                    'port' => $options['port']]
            );
        }

        if (!is_readable($options['socket'])) {
            throw new ConfigurationException(
                sprintf('Socket "%s" does not exist or is not readable.', $options['socket'])
            );
        }
        return new Socket(['path' => $options['socket']]);
    }
}
