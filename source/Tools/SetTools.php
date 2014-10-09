<?php
namespace Grout\Cyantree\ManagedModule\Tools;

use Cyantree\Grout\Filter\ArrayFilter;
use Grout\Cyantree\ManagedModule\ManagedFactory;

class SetTools
{
    /** @var ManagedFactory */
    private $factory;

    public function __construct(ManagedFactory $factory)
    {
        $this->factory = $factory;
    }

    public function register($id, $setClass, $config = array())
    {
        $config = new ArrayFilter($config);

        $this->factory->module->setTypes->set($id, $setClass);
        $this->factory->module->setTypeConfigs->set($id, $config);

        if ($page = $config->get('ListPage')) {
            $this->factory->module->addRoute('list-sets/' . $id . '/', $page, array('type' => $id), 1);
            $this->factory->module->addRoute('export-sets/' . $id . '/export.%%format%%', $page, array('mode' => 'export', 'type' => $id), 1);
        }

        if ($page = $config->get('EditPage')) {
            $this->factory->module->addRoute('edit-set/' . $id . '/%%id%%/', $page, array('type' => $id), 1);
        }

        if ($page = $config->get('AddPage')) {
            $this->factory->module->addRoute('add-set/' . $id . '/', $page, array('type' => $id), 1);
        }

        if ($page = $config->get('DeletePage')) {
            $this->factory->module->addRoute('delete-set/' . $id . '/%%id%%/', $page, array('type' => $id), 1);
        }
    }
}
