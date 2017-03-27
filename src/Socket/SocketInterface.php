<?php
namespace Cacko\ClamAv\Socket;

use SplFileObject;

/**
 * Interface SocketInterface
 * @package Cacko\ClamAv\Socket
 */
interface SocketInterface
{
    /**
     * @return void
     */
    public function close();

    /**
     * @param $data
     * @param int $flags
     * @return false|int
     */
    public function send($data, $flags = 0);

    /**
     * @param $resource
     * @return false|int
     */
    public function streamResource(SplFileObject $resource);

    /**
     * @param $data
     * @return false|int
     */
    public function streamData($data);

    /**
     * @param int $flags
     * @return string|false
     */
    public function receive($flags = MSG_WAITALL);
}
