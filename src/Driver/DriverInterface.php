<?php

namespace Cacko\ClamAv\Driver;

use SplFileObject;

/**
 * Interface DriverInterface
 * @package Cacko\ClamAv\Driver
 */
interface DriverInterface
{

    public function scan(string $path): array;

    public function scanBuffer(string $buffer): array;

    public function scanResource(SplFileObject $object): array;

    public function ping(): bool;

    public function version(): string;

}
