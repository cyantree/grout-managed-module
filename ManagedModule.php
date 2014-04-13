<?php
namespace Grout\Cyantree\ManagedModule;

use Cyantree\Grout\App\Module;
use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Filter\ArrayFilter;
use Grout\Cyantree\ManagedModule\Types\ManagedConfig;
use Grout\Cyantree\ManagedModule\Types\ManagedPlugin;

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
    public $setTypeConfigs = null;

    public function init()
    {
        $this->setTypes = new ArrayFilter();
        $this->setTypeConfigs = new ArrayFilter();

        $this->app->configs->setDefaultConfig($this->id, new ManagedConfig());
        $this->moduleConfig = $this->app->configs->getConfig($this->id);

        $this->plugins = array();

        $this->defaultPageType = 'Pages\RestrictedPage';
        $this->addNamedRoute('index', '', null, array('template' => 'index.html'));

        $this->addNamedRoute('service', 'service/', 'Pages\ServicePage');
        $this->addNamedRoute('logout', 'logout/', 'Pages\LogoutPage');

        // Entity pages
        $this->addNamedRoute('list-sets', 'list-sets/%%type%%/', 'Pages\Sets\ListSetsPage');
        $this->addNamedRoute('add-set', 'add-set/%%type%%/', 'Pages\Sets\EditSetPage');
        $this->addNamedRoute('edit-set', 'edit-set/%%type%%/%%id%%/', 'Pages\Sets\EditSetPage');
        $this->addNamedRoute('delete-set', 'delete-set/%%type%%/%%id%%/', 'Pages\Sets\DeleteSetPage');
        $this->addNamedRoute('404', '%%any,.*%%', null, array('template' => '404.html', 'responseCode' => ResponseCode::CODE_404), -1);

        foreach ($this->moduleConfig->plugins as $plugin) {
            $this->importPlugin($plugin['plugin'], $plugin);
        }
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