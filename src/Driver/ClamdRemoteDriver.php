<?php

namespace Cacko\ClamAv\Driver;

use Cacko\ClamAv\Exception\RuntimeException;
use SplFileObject;

/**
 * Class ClamdRemoteDriver
 * @package Cacko\ClamAv\Driver
 */
class ClamdRemoteDriver extends ClamdDriver
{
    /**
     * @var string
     */
    const SOCKET_PATH = '';

    /**
     * ClamdRemoteDriver constructor.
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        unset($options['socket']);
        parent::__construct($options);
    }

    public function scan(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException('Remote scan of directory is not supported');
        }

        $this->sendCommand('INSTREAM');

        $resource = new SplFileObject($path, 'rb');

        $this->socket()->streamResource($resource);

        $result = $this->getResponse();

        if (false != ($filtered = $this->filterScanResult($result))) {
            $filtered[0] = preg_replace('/^stream:/', $path . ':', $filtered[0]);
        }

        return $filtered;
    }
}
