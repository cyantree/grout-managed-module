<?php
namespace Grout\Cyantree\ManagedModule\Tools;

use Cyantree\Grout\Filter\ArrayFilter;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\ManagedModule;

class SetTools {
    /** @var ManagedFactory */
    private $_factory;

    /** @var  ManagedModule */
    private $_module;

    public function __construct(ManagedFactory $factory)
    {
        $this->_factory = $factory;
        $this->_module = $factory->appModule();
    }

    public function register($id, $setClass, $entityClass, $config = array())
    {
        $config = new ArrayFilter($config);

        $this->_module->setTypes->set($id, $setClass);
        $this->_module->setTypeEntities->set($id, $entityClass);
        $this->_module->setTypeConfigs->set($id, $config);

        if ($config->get('ListPage')) {
            $this->_module->addRoute('list-sets/'.$id.'/', $config->get('ListPage'), array('type' => $id), 1);
        }
    }
}