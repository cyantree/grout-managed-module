<?php
namespace Grout\ManagedModule;

use Cyantree\Grout\App\Module;
use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Filter\ArrayFilter;
use Grout\ManagedModule\Types\ManagedConfig;
use Grout\ManagedModule\Types\ManagedPlugin;

class ManagedModule extends Module
{
    public static $_CLASS_ = __CLASS__;

    /** @var ManagedConfig */
    public $moduleConfig;

    /** @var ManagedPlugin[] */
    public $plugins;

    public $menuLinks = array();

    /** @var ArrayFilter */
    public $setTypes = null;

    /** @var ArrayFilter */
    public $setTypeEntities = null;

    public function init()
    {
        $this->setTypes = new ArrayFilter();
        $this->setTypeEntities = new ArrayFilter();

        $this->moduleConfig = $this->app->config->get($this->type, $this->id, new ManagedConfig());

        $this->plugins = array();

        foreach($this->moduleConfig->plugins as $plugin){
            $p = $this->importPlugin($plugin['plugin'], $plugin);
        }

        $this->defaultPageType = 'RestrictedPage';
        $this->addNamedRoute('index', '', null, array('template' => 'index.html'));

        $this->addNamedRoute('service', 'service/', 'ServicePage');
        $this->addNamedRoute('logout', 'logout/', 'LogoutPage');

        // Entity pages
        $this->addNamedRoute('list-sets', 'list-sets/%%type%%/', 'Sets\ListSetsPage');
        $this->addNamedRoute('add-set', 'add-set/%%type%%/', 'Sets\EditSetPage');
        $this->addNamedRoute('edit-set', 'edit-set/%%type%%/%%id%%/', 'Sets\EditSetPage');
        $this->addNamedRoute('delete-set', 'delete-set/%%type%%/%%id%%/', 'Sets\DeleteSetPage');
        $this->addNamedRoute('404', '%%any,.*%%', null, array('template' => '404.html', 'responseCode' => ResponseCode::CODE_404), -1);
    }

    public function initTask($task)
    {
        foreach($this->plugins as $plugin){
            $plugin->initTask($task);
        }
    }


    public function beforeParsing($task)
    {
        if($task->plugin){
            $task->plugin->beforeParsing($task);
        }
    }

    public function afterParsing($task)
    {
        if($task->plugin){
            $task->plugin->afterParsing($task);
        }
    }
}