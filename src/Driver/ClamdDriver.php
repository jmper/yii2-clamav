<?php

namespace Cacko\ClamAv\Driver;

use Cacko\ClamAv\Exception\RuntimeException;
use Cacko\ClamAv\Socket\Socket;
use Cacko\ClamAv\Socket\SocketFactory;
use Cacko\ClamAv\Socket\SocketInterface;
use SplFileObject;

/**
 * Class ClamdDriver
 * @package Cacko\ClamAv\Driver
 */
class ClamdDriver extends AbstractDriver
{
    /**
     * @var string
     */
    const HOST = '127.0.0.1';

    /**
     * @var int
     */
    const PORT = 3310;

    /**
     * @var string
     */
    const SOCKET_PATH = '/var/run/clamav/clamd.sock';

    /**
     * @var string
     */
    const COMMAND = "n%s\n";

    /**
     * @var Socket
     */
    protected $socketInstance;

    public function ping(): bool
    {
        $this->sendCommand('PING');
        return trim($this->getResponse()) === 'PONG';
    }

    public function version(): string
    {
        $this->sendCommand('VERSION');
        return trim($this->getResponse());
    }

    public function scan(string $path): array
    {
        if (is_dir($path)) {
            $command = 'CONTSCAN';
        } else {
            $command = 'SCAN';
        }

        $this->sendCommand($command . ' ' . $path);

        $result = $this->getResponse();

        return $this->filterScanResult($result);
    }

    public function scanBuffer(string $buffer): array
    {
        $this->sendCommand('INSTREAM');

        $this->socket()->streamData($buffer);

        $result = $this->getResponse();

        if (false != ($filtered = $this->filterScanResult($result))) {
            $filtered[0] = preg_replace('/^stream:/', 'buffer:', $filtered[0]);
        }

        return $filtered;
    }

    public function scanResource(SplFileObject $object): array
    {
        $this->sendCommand('INSTREAM');

        $this->socket()->streamResource($object);

        $result = $this->getResponse();

        if (false != ($filtered = $this->filterScanResult($result))) {
            $filtered[0] = preg_replace('/^stream:/', $object->getFilename() . ':', $filtered[0]);
        }

        return $filtered;
    }

    /**
     * @param string $command
     * @return int|false
     */
    protected function sendCommand(string $command)
    {
        return $this->sendRequest(sprintf(static::COMMAND, $command));
    }

    /**
     * @param SocketInterface $socket
     */
    public function setSocket(SocketInterface $socket)
    {
        $this->socket = $socket;
    }


    protected function socket(): SocketInterface
    {
        if (!$this->socketInstance) {
            if ($this->socket) { // socket set in config
                $options = [
                    'socket' => $this->socket
                ];
            } elseif ($this->host) { // host set in config
                $options = [
                    'host' => $this->host,
                    'port' => $this->port ?: static::PORT
                ];
            } else { // use defaults
                $options = [
                    'socket' => $this->socket ?: static::SOCKET_PATH,
                    'host' => $this->host ?: static::HOST,
                    'port' => $this->port ?: static::PORT
                ];
            }
            $this->socketInstance = SocketFactory::create($options);
        }

        return $this->socketInstance;
    }

    /**
     * @param $data
     * @param int $flags
     * @return false|int
     * @throws RuntimeException
     */
    protected function sendRequest($data, $flags = 0)
    {
        if (false == ($bytes = $this->socket()->send($data, $flags))) {
            throw new RuntimeException('Cannot write to socket');
        }
        return $bytes;
    }

    /**
     * @param int $flags
     * @return string|false
     */
    protected function getResponse($flags = MSG_WAITALL)
    {
        $data = $this->socket()->receive($flags);
        $this->socket()->close();
        return $data;
    }

    protected function filterScanResult(string $result, $filter = 'FOUND'): array
    {
        $result = explode("\n", $result);
        $result = array_filter($result);

        $list = [];
        foreach ($result as $line) {
            if (substr($line, -5) === $filter) {
                $list[] = $line;
            }
        }
        return $list;
    }
}
