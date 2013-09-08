<?php
namespace Grout\ManagedModule\Tools;

use Cyantree\Grout\Filter\ArrayFilter;
use Grout\ManagedModule\ManagedFactory;
use Grout\ManagedModule\ManagedModule;

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
//        $config->set('ListPage', $config->get('ListPage', 'ListSetsPage'));

        $this->_module->setTypes->set($id, $setClass);
        $this->_module->setTypeEntities->set($id, $entityClass);
        $this->_module->setTypeConfigs->set($id, $config);

        if ($config->get('ListPage')) {
            $this->_module->addRoute('list-sets/'.$id.'/', $config->get('ListPage'), array('type' => $id), 1);
        }
    }
}