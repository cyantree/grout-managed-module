<?php
namespace Grout\Cyantree\ManagedModule\Tools;

use Cyantree\Grout\App\Route;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Types\AccessRule;

class MenuTools
{
    /** @var ManagedFactory */
    private $_factory;

    private $_links;

    public function __construct(ManagedFactory $factory)
    {
        $this->_factory = $factory;
        $this->_links = &$this->_factory->appModule()->menuLinks;
    }


    public function addUrlLink($title, $url, AccessRule $accessRule = null, $config = array())
    {
        $this->_links[] = array_merge(array(
            'type' => 'url',
            'url' => $url,
            'title' => $title,
            'access' => $accessRule
        ), $config);
    }

    public function addListSetsLink($title, $set, AccessRule $accessRule = null, $config = array())
    {
        $this->_links[] = array_merge(array(
            'type' => 'url',
            'url' => $this->_factory->appModule()->getRouteUrl('list-sets', array('type' => $set)),
            'access' => $accessRule,
            'title' => $title,
            'id' => $set.'-sets'
        ), $config);
    }

    public function addEditSetLink($title, $set, $id, AccessRule $accessRule = null, $config = array())
    {
        $this->_links[] = array_merge(array(
            'type' => 'url',
            'url' => $this->_factory->appModule()->getRouteUrl('edit-set', array('type' => $set, 'id' => $id)),
            'access' => $accessRule,
            'title' => $title,
            'id' => $set.'-sets'
        ), $config);
    }

    public function addAddSetLink($title, $set, AccessRule $accessRule = null, $config = array())
    {
        $this->_links[] = array_merge(array(
                'type' => 'url',
                'url' => $this->_factory->appModule()->getRouteUrl('add-set', array('type' => $set)),
                'access' => $accessRule,
                'title' => $title,
                'id' => $set.'-sets'
            ), $config);
    }

    public function addDeleteSetLink($title, $set, $id, AccessRule $accessRule = null, $config = array())
    {
        $this->_links[] = array_merge(array(
            'type' => 'url',
            'url' => $this->_factory->appModule()->getRouteUrl('delete-set', array('type' => $set, 'id' => $id)),
            'access' => $accessRule,
            'title' => $title,
            'id' => $set.'-sets'
        ), $config);
    }

    public function addRouteLink($title, Route $route, $arguments = null, AccessRule $accessRule = null, $config = array())
    {
        $this->_links[] = array_merge(array(
            'type' => 'url',
            'route' => $route,
            'url' => $route->getUrl($arguments),
            'access' => $accessRule,
            'title' => $title,
        ), $config);
    }
}