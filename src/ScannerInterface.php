<?php
namespace Cacko\ClamAv;

use SplFileObject;

/**
 * Interface ScannerInterface
 * @package Cacko\ClamAv
 */
interface ScannerInterface
{

    public function scan(string $path): ResultInterface;

    public function scanBuffer(string $buffer): ResultInterface;

    public function scanResource(SplFileObject $object): ResultInterface;

    public function ping(): bool;

    public function version(): string;
}
