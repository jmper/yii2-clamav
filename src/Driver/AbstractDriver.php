<?php

namespace Cacko\ClamAv\Driver;

use yii\base\Component;

/**
 * Class AbstractDriver
 * @package Cacko\ClamAv\Driver
 */
abstract class AbstractDriver extends Component implements DriverInterface
{

    public $executable;

    public $socket;

    public $host;

    public $port;

}
