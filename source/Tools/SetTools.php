<?php
namespace Grout\Cyantree\ManagedModule\Tools;

use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Types\RegisterSetConfig;

class SetTools
{
    /** @var ManagedFactory */
    private $factory;

    public function __construct(ManagedFactory $factory)
    {
        $this->factory = $factory;
    }

    /** @return RegisterSetConfig */
    public function getConfig($id)
    {
        return $this->factory->module->setTypeConfigs->needs($id);
    }

    public function register($id, $setClass, RegisterSetConfig $config = null)
    {
        if (!$config) {
            $config = new RegisterSetConfig();
        }

        $this->factory->module->setTypes->set($id, $setClass);
        $this->factory->module->setTypeConfigs->set($id, $config);

        if (!$config->listPageAccess) {
            $config->listPageAccess = $this->factory->module->moduleConfig->aclRule;
        }
        if ($config->listPage || $config->listPageAccess) {
            $page = $config->listPage ? $config->listPage : 'Pages\Sets\ListSetsPage';
            $route = $this->factory->module->addRoute(
                'list-sets/' . $id . '/',
                $page,
                array('type' => $id),
                1
            );

            if ($config->listPageAccess) {
                $this->factory->acl()->secureRoute(
                    $route,
                    $config->listPageAccess,
                    $this->factory->config()->title,
                    $this->factory->module->id . '::Pages\Acl\LoginPage'
                );
            }
        }

        if (!$config->exportAccess) {
            $config->exportAccess = $this->factory->module->moduleConfig->aclRule;
        }
        if ($config->listPage || $config->exportAccess) {
            $page = $config->listPage ? $config->listPage : 'Pages\Sets\ListSetsPage';
            $route = $this->factory->module->addRoute(
                'export-sets/' . $id . '/export.%%format%%',
                $page,
                array('mode' => 'export', 'type' => $id),
                1
            );

            if ($config->exportAccess) {
                $this->factory->acl()->secureRoute(
                    $route,
                    $config->exportAccess,
                    $this->factory->config()->title,
                    $this->factory->module->id . '::Pages\Acl\LoginPage'
                );
            }
        }

        if (!$config->editPageAccess) {
            $config->editPageAccess = $this->factory->module->moduleConfig->aclRule;
        }
        if ($config->editPage || $config->editPageAccess) {
            $page = $config->editPage ? $config->editPage : 'Pages\Sets\EditSetPage';
            $route = $this->factory->module->addRoute(
                'edit-set/' . $id . '/%%id%%/',
                $page,
                array('type' => $id),
                1
            );

            if ($config->editPageAccess) {
                $this->factory->acl()->secureRoute(
                    $route,
                    $config->editPageAccess,
                    $this->factory->config()->title,
                    $this->factory->module->id . '::Pages\Acl\LoginPage'
                );
            }
        }

        if (!$config->addPageAccess) {
            $config->addPageAccess = $this->factory->module->moduleConfig->aclRule;
        }
        if ($config->addPage || $config->addPageAccess) {
            $page = $config->addPage ? $config->addPage : 'Pages\Sets\EditSetPage';
            $route = $this->factory->module->addRoute(
                'add-set/' . $id . '/',
                $page,
                array('type' => $id),
                1
            );

            if ($config->addPageAccess) {
                $this->factory->acl()->secureRoute(
                    $route,
                    $config->addPageAccess,
                    $this->factory->config()->title,
                    $this->factory->module->id . '::Pages\Acl\LoginPage'
                );
            }
        }

        if (!$config->deletePageAccess) {
            $config->deletePageAccess = $this->factory->module->moduleConfig->aclRule;
        }
        if ($config->deletePage || $config->deletePageAccess) {
            $page = $config->deletePage ? $config->deletePage : 'Pages\Sets\DeleteSetPage';
            $route = $this->factory->module->addRoute(
                'delete-set/' . $id . '/%%id%%/',
                $page,
                array('type' => $id),
                1
            );

            if ($config->deletePageAccess) {
                $this->factory->acl()->secureRoute(
                    $route,
                    $config->deletePageAccess,
                    $this->factory->config()->title,
                    $this->factory->module->id . '::Pages\Acl\LoginPage'
                );
            }
        }
    }
}
