<?php
namespace Grout\Cyantree\ManagedModule\Pages;

use Cyantree\Grout\App\Page;
use Cyantree\Grout\App\Service\Drivers\JsonDriver;
use Cyantree\Grout\App\Service\ServiceDriver;
use Grout\Cyantree\ManagedModule\ManagedModule;

class ServicePage extends Page
{
    /** @var ServiceDriver */
    private $_driver;

    public function parseTask()
    {
        $this->_driver = new JsonDriver();
        $this->_driver->commandNamespaces[] = $this->task->module->namespace.'Commands\\';

        /** @var ManagedModule $m */
        $m = $this->task->module;
        foreach($m->plugins as $plugin){
            if($plugin->extendsService){
                $this->_driver->commandNamespaces[] = $plugin->namespace.'Commands\\';
            }
        }

        $this->_driver->processTask($this->task);
    }

public function parseError($code, $data = null)
    {
        if($this->_driver){
            $this->_driver->processError($this->task);
        }else{
            parent::parseError($code, $data);
        }
    }
}