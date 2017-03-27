<?php

namespace Cacko\ClamAv;

use yii\base\Component;

/**
 * Interface ResultInterface
 * @package Cacko\ClamAv
 */
class Result extends Component implements ResultInterface
{

    public $path;

    /**
     * @var array
     */
    protected $infected = [];

    public function isClean(): bool
    {
        return !$this->isInfected();
    }

    public function isInfected(): bool
    {
        return !!count($this->infected);
    }

    public function getInfected(): array
    {
        return $this->infected;
    }

    public function addInfected(string $file, string $virus)
    {
        $this->infected[$file] = $virus;
    }


    public function setInfected(array $infected)
    {
        $this->infected = $infected;
    }

    public function __toString(): string
    {
        $str = [];
        foreach ((array)$this->infected as $k => $v) {
            $str[] = $k . ': ' . $v;
        }
        return join(PHP_EOL, $str);
    }
}
