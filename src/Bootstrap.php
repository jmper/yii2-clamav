<?php

namespace Cacko\ClamAv;

use Cacko\ClamAv\Driver\DriverFactory;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{

    public function bootstrap($app)
    {
        if (!$app->has(Scanner::COMPONENT_ID)) {
            $app->set(
                Scanner::COMPONENT_ID,
                [
                    'class' => Scanner::class,
                    'driver' => DriverFactory::DRIVER_CLAMSCAN
                ]
            );
        }
    }
}
