<?php

namespace Cacko\ClamAv\Driver;


use SplFileObject;

class DummyDriver extends AbstractDriver
{

    public function scan(string $path): array
    {
        return [];
    }

    public function scanBuffer(string $buffer): array
    {
        return [];
    }

    public function scanResource(SplFileObject $object): array
    {
        return [];
    }

    public function ping(): bool
    {
        return true;
    }

    public function version(): string
    {
        return 'dummy';
    }
}