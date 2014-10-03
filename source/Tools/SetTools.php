<?php
namespace Grout\Cyantree\ManagedModule\Tools;

use Cyantree\Grout\Filter\ArrayFilter;
use Grout\Cyantree\ManagedModule\ManagedFactory;

class SetTools {
    /** @var ManagedFactory */
    private $_factory;

    public function __construct(ManagedFactory $factory)
    {
        $this->_factory = $factory;
    }

    public function register($id, $setClass, $config = array())
    {
        $config = new ArrayFilter($config);

        $this->_factory->module->setTypes->set($id, $setClass);
        $this->_factory->module->setTypeConfigs->set($id, $config);

        if ($page = $config->get('ListPage')) {
            $this->_factory->module->addRoute('list-sets/' . $id . '/', $page, array('type' => $id), 1);
            $this->_factory->module->addRoute('export-sets/' . $id . '/export.%%format%%', $page, array('mode' => 'export', 'type' => $id), 1);
        }

        if ($page = $config->get('EditPage')) {
            $this->_factory->module->addRoute('edit-set/' . $id . '/%%id%%/', $page, array('type' => $id), 1);
        }

        if ($page = $config->get('AddPage')) {
            $this->_factory->module->addRoute('add-set/' . $id . '/', $page, array('type' => $id), 1);
        }

        if ($page = $config->get('DeletePage')) {
            $this->_factory->module->addRoute('delete-set/' . $id . '/%%id%%/', $page, array('type' => $id), 1);
        }
    }
}