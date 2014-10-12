<?php
namespace Grout\Cyantree\ManagedModule\Tools;

use Cyantree\Grout\App\Route;
use Grout\Cyantree\AclModule\AclFactory;
use Grout\Cyantree\AclModule\Types\AclRule;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Types\AccessRule;

class MenuTools
{
    /** @var ManagedFactory */
    private $factory;

    private $links;

    public function __construct(ManagedFactory $factory)
    {
        $this->factory = $factory;
        $this->links = &$this->factory->module->menuLinks;
    }


    public function addUrlLink($title, $url, AclRule $accessRule = null, $config = array())
    {
        $this->links[] = array_merge(array(
            'type' => 'url',
            'url' => $url,
            'title' => $title,
            'access' => $accessRule
        ), $config);
    }

    public function addListSetsLink($title, $set, AclRule $accessRule = null, $config = array())
    {
        $this->links[] = array_merge(array(
            'type' => 'url',
            'url' => $this->factory->module->getRouteUrl('list-sets', array('type' => $set)),
            'access' => $accessRule,
            'title' => $title,
            'id' => $set . '-sets'
        ), $config);
    }

    public function addEditSetLink($title, $set, $id, AclRule $accessRule = null, $config = array())
    {
        $this->links[] = array_merge(array(
            'type' => 'url',
            'url' => $this->factory->module->getRouteUrl('edit-set', array('type' => $set, 'id' => $id)),
            'access' => $accessRule,
            'title' => $title,
            'id' => $set . '-sets'
        ), $config);
    }

    public function addAddSetLink($title, $set, AclRule $accessRule = null, $config = array())
    {
        $this->links[] = array_merge(array(
                'type' => 'url',
                'url' => $this->factory->module->getRouteUrl('add-set', array('type' => $set)),
                'access' => $accessRule,
                'title' => $title,
                'id' => $set . '-sets'
            ), $config);
    }

    public function addDeleteSetLink($title, $set, $id, AclRule $accessRule = null, $config = array())
    {
        $this->links[] = array_merge(array(
            'type' => 'url',
            'url' => $this->factory->module->getRouteUrl('delete-set', array('type' => $set, 'id' => $id)),
            'access' => $accessRule,
            'title' => $title,
            'id' => $set . '-sets'
        ), $config);
    }

    public function addRouteLink($title, Route $route, $arguments = null, AclRule $accessRule = null, $config = array())
    {
        $this->links[] = array_merge(array(
            'type' => 'url',
            'route' => $route,
            'url' => $route->getUrl($arguments),
            'access' => $accessRule,
            'title' => $title,
        ), $config);
    }
}
