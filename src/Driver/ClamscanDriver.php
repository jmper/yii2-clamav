<?php

namespace Cacko\ClamAv\Driver;

use Cacko\ClamAv\Exception\ConfigurationException;
use Cacko\ClamAv\Exception\RuntimeException;
use SplFileObject;

/**
 * Class ClamscanDriver
 * @package Cacko\ClamAv\Driver
 */
class ClamscanDriver extends AbstractDriver
{

    /**
     * @var int
     */
    const BYTES_WRITE = 8192;

    /**
     * @var string
     */
    const EXECUTABLE = '/usr/bin/clamscan';

    /**
     * @var string
     */
    const COMMAND = '--infected --no-summary --recursive %s';

    /**
     * @var int
     */
    const CLEAN = 0;

    /**
     * @var int
     */
    const INFECTED = 1;

    /**
     * ClamscanDriver constructor.
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        parent::__construct($config);

        if (!$this->executable) {
            $this->executable = static::EXECUTABLE;
        }

        if (!is_executable($this->executable)) {
            throw new ConfigurationException(
                $this->executable ?
                    sprintf('%s is not valid executable file', $this->executable) :
                    'Executable required, please check your config.'
            );
        }
    }

    public function ping(): bool
    {
        return !!$this->version();
    }

    public function version(): string
    {
        exec($this->executable . ' -V', $out, $return);
        if (!$return) {
            return $out[0];
        }
        return '';
    }

    public function scan($path): array
    {
        $safe_path = escapeshellarg($path);

        // Reset the values.
        $return = -1;

        $cmd = $this->executable . ' ' . sprintf(static::COMMAND, $safe_path);

        // Execute the command.
        exec($cmd, $out, $return);

        return $this->parseResults($return, $out);
    }

    public function scanBuffer($buffer): array
    {
        return $this->scanPipe(function (&$pipe) use ($buffer) {
            fwrite($pipe, $buffer);
        });
    }

    public function scanResource(SplFileObject $object): array
    {
        return $this->scanPipe(function (&$pipe) use ($object) {
            while ($chunk = $object->fread(static::BYTES_WRITE)) {
                fwrite($pipe, $chunk);
            }
        });
    }

    protected function scanPipe(\Closure $writeToPipe): array
    {
        $descriptorSpec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
        ];

        $cmd = $this->executable . ' ' . sprintf(static::COMMAND, '-');

        $process = @ proc_open($cmd, $descriptorSpec, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to open a process file pointer');
        }

        $writeToPipe($pipes[0]);

        fclose($pipes[0]);

        // get response
        $out = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        // get return value and close
        $return = proc_close($process);

        if (false != ($parsed = $this->parseResults($return, explode("\n", $out)))) {
            $parsed[0] = preg_replace('/^stream:/', 'buffer:', $parsed[0]);
        }

        return $parsed;
    }

    protected function parseResults(int $return, array $out): array
    {
        $result = [];
        if ($return == static::INFECTED) {
            foreach ($out as $infected) {
                if (empty($infected)) {
                    break;
                }
                $result[] = $infected;
            }
        }

        return $result;
    }
}
