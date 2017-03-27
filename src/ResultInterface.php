<?php
namespace Cacko\ClamAv;

/**
 * Interface ResultInterface
 * @package Cacko\ClamAv
 */
interface ResultInterface
{

    public function isClean(): bool;

    public function isInfected(): bool;

    public function getInfected(): array;

}
