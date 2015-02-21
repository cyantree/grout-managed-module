<?php
namespace Grout\Cyantree\ManagedModule\Pages;

use Cyantree\Grout\App\Page;
use Cyantree\Grout\App\Service\Drivers\JsonDriver;
use Cyantree\Grout\App\Service\ServiceDriver;
use Grout\Cyantree\ManagedModule\ManagedModule;

class ServicePage extends Page
{
    /** @var ServiceDriver */
    private $driver;

    public function parseTask()
    {
        $this->driver = new JsonDriver();
        $this->driver->commandNamespaces[] = $this->task->module->definition->namespace . 'Commands\\';

        /** @var ManagedModule $m */
        $m = $this->task->module;
        foreach ($m->plugins as $plugin) {
            if ($plugin->extendsService) {
                $this->driver->commandNamespaces[] = $plugin->definition->namespace . 'Commands\\';
            }
        }

        $this->driver->processTask($this->task);
    }

    public function parseError($code, $data = null)
    {
        if ($this->driver) {
            $this->driver->processError($this->task);

        } else {
            parent::parseError($code, $data);
        }
    }
}
