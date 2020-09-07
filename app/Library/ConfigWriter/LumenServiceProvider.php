<?php

namespace App\Library\ConfigWriter;

use Laravel\Lumen\Application;

class LumenServiceProvider extends ServiceProvider
{
    /** @var  Application */
    protected $app;

    protected function getConfigPath(): string
    {
        return $this->app->getConfigurationPath();
    }
}
