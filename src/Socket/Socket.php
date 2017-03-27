<?php

namespace Cacko\ClamAv\Socket;

use Cacko\ClamAv\Exception\RuntimeException;
use Cacko\ClamAv\Exception\SocketException;
use SplFileObject;
use yii\base\Component;

/**
 * Class Socket
 * @package Cacko\ClamAv\Socket
 */
class Socket extends Component implements SocketInterface
{
    /**
     * @var int
     */
    const BYTES_READ = 8192;

    /**
     * @var int
     */
    const BYTES_WRITE = 8192;

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    /**
     * @var int
     */
    public $path;


    /**
     * @return void
     */
    public function close()
    {
        if (is_resource($this->socket)) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }

    /**
     * @param $data
     * @param int $flags
     * @return false|int
     * @throws SocketException
     */
    public function send($data, $flags = 0)
    {
        $this->reconnect();

        return socket_send($this->socket, $data, strlen($data), $flags);
    }

    /**
     * @param SplFileObject $resource
     * @return false|int
     * @throws SocketException
     */
    public function streamResource(SplFileObject $resource)
    {
        $this->reconnect();

        $result = 0;
        while ($chunk = $resource->fread(self::BYTES_WRITE)) {
            $result += $this->sendChunk($chunk);
        }

        $result += $this->endStream();

        return $result;
    }

    /**
     * @param $data
     * @return false|int
     * @throws SocketException
     */
    public function streamData($data)
    {
        $this->reconnect();

        $result = 0;
        $left = $data;
        while (strlen($left) > 0) {
            $chunk = substr($left, 0, self::BYTES_WRITE);
            $left = substr($left, self::BYTES_WRITE);
            $result += $this->sendChunk($chunk);
        }

        $result += $this->endStream();

        return $result;
    }

    /**
     * @param int $flags
     * @return string|false
     * @throws RuntimeException
     */
    public function receive($flags = MSG_WAITALL)
    {
        // $this->reconnect();
        if (!is_resource($this->socket)) {
            throw new RuntimeException('Socket is currently closed');
        }

        $data = '';
        while ($bytes = socket_recv($this->socket, $chunk, self::BYTES_READ, $flags)) {
            $data .= $chunk;
        }

        return $data;
    }

    /**
     * @param $chunk
     * @return false|int
     * @throws SocketException
     */
    protected function sendChunk($chunk)
    {
        $size = pack('N', strlen($chunk));
        // size packet
        $result = $this->send($size);
        // data packet
        $result += $this->send($chunk);
        return $result;
    }

    /**
     * @return false|int
     * @throws SocketException
     */
    protected function endStream()
    {
        $packet = pack('N', 0);
        return $this->send($packet);
    }

    /**
     * @throws SocketException
     */
    protected function connect(): void
    {
        $this->socket = $this->path ?
            $this->unixSocket() :
            $this->inetSocket();
    }

    /**
     * @return resource
     * @throws SocketException
     */
    protected function inetSocket()
    {
        $socket = @socket_create(AF_INET, SOCK_STREAM, 0);
        if ($socket === false) {
            throw new SocketException('', socket_last_error());
        }
        $hasError = @ socket_connect(
            $socket,
            $this->host,
            $this->port
        );
        if ($hasError === false) {
            throw new SocketException('', socket_last_error());
        }
        return $socket;
    }

    /**
     * @return resource
     * @throws SocketException
     */
    protected function unixSocket()
    {
        $socket = @ socket_create(AF_UNIX, SOCK_STREAM, 0);
        if ($socket === false) {
            throw new SocketException('', socket_last_error());
        }
        $hasError = @ socket_connect($socket, $this->path);
        if ($hasError === false) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);
            throw new SocketException($errorMessage, $errorCode);
        }
        return $socket;
    }

    /**
     * @throws SocketException
     */
    protected function reconnect(): void
    {
        if (!is_resource($this->socket)) {
            $this->connect();
        }
    }
}
