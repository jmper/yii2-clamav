<?php

namespace Cacko\ClamAv;

use Cacko\ClamAv\Driver\DriverFactory;
use Cacko\ClamAv\Driver\DriverInterface;
use Cacko\ClamAv\Exception\RuntimeException;
use SplFileObject;
use yii\base\Component;

class Scanner extends Component implements ScannerInterface
{
    /**
     * @var DriverInterface
     */
    protected $driverInstance;

    protected $config;

    public $driver;

    public $executable;

    public $socket;

    public $host;

    public $port;

    const COMPONENT_ID = 'clamav';

    public function __construct($config = [])
    {
        $this->config = $config;
        parent::__construct($config);
    }

    public function ping(): bool
    {
        return $this->driver()->ping();
    }

    public function version(): string
    {
        return $this->driver()->version();
    }

    public function scan($path): ResultInterface
    {
        if (!is_readable($path)) {
            throw new RuntimeException(
                sprintf('"%s" does not exist or is not readable.')
            );
        }

        $real_path = realpath($path);

        return $this->parseResults(
            $path,
            $this->driver()->scan($real_path)
        );
    }

    public function scanBuffer($buffer): ResultInterface
    {
        if (!is_scalar($buffer) && (!is_object($buffer) || !method_exists($buffer, '__toString'))) {
            throw new RuntimeException(
                sprintf('Expected scalar value, received %s', gettype($buffer))
            );
        }

        return $this->parseResults(
            'buffer',
            $this->driver()->scanBuffer($buffer)
        );
    }


    public function scanResource(SplFileObject $object): ResultInterface
    {
        return $this->parseResults(
            $object->getFilename(),
            $this->driver()->scanResource($object)
        );
    }

    protected function driver(): DriverInterface
    {
        if (!$this->driverInstance) {
            $this->driverInstance = DriverFactory::create($this->config);
        }
        return $this->driverInstance;
    }

    protected function parseResults($path, array $infected): ResultInterface
    {
        $result = new Result(['path' => $path]);

        foreach ($infected as $line) {
            list($file, $virus) = explode(':', $line);
            $result->addInfected($file, preg_replace('/ FOUND$/', '', $virus));
        }

        return $result;
    }
}
