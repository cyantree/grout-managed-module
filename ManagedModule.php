<?php
namespace Grout\Cyantree\ManagedModule;

use Cyantree\Grout\App\Module;
use Cyantree\Grout\App\Task;
use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Filter\ArrayFilter;
use Grout\Cyantree\ManagedModule\Types\ManagedConfig;
use Grout\Cyantree\ManagedModule\Types\ManagedPlugin;

class ManagedModule extends Module
{
    public static $CLASS = __CLASS__;

    /** @var ManagedConfig */
    public $moduleConfig;

    /** @var ManagedPlugin[] */
    public $plugins;

    public $menuLinks = array();

    /** @var ArrayFilter */
    public $setTypes = null;

    /** @var ArrayFilter */
    public $setTypeConfigs = null;

    /** @var ManagedFactory */
    private $factory;

    public function factory()
    {
        if ($this->factory === null) {
            $this->factory = ManagedFactory::get($this->app, $this->id);
        }

        return $this->factory;
    }

    public function init()
    {
        $this->setTypes = new ArrayFilter();
        $this->setTypeConfigs = new ArrayFilter();

        $config = new ManagedConfig();
        $config->setContext = $this->id;

        $this->app->configs->setDefaultConfig($this->id, $config);
        $this->moduleConfig = $this->app->configs->getConfig($this->id);

        $this->plugins = array();

        $this->defaultPageType = $this->generateContextString('Pages\ManagedPage');
        $this->addNamedRoute('index', '', null, array('template' => 'index.html'));

        $this->addNamedRoute('service', 'service/', 'Pages\ServicePage');

        // Entity pages
        $this->addNamedRoute('list-sets', 'list-sets/%%type%%/', 'Pages\Sets\ListSetsPage');
        $this->addNamedRoute('export-sets', 'export-sets/%%type%%/export.%%format%%', 'Pages\Sets\ListSetsPage', array('mode' => 'export'));
        $this->addNamedRoute('add-set', 'add-set/%%type%%/', 'Pages\Sets\EditSetPage');
        $this->addNamedRoute('edit-set', 'edit-set/%%type%%/%%id%%/', 'Pages\Sets\EditSetPage');
        $this->addNamedRoute('delete-set', 'delete-set/%%type%%/%%id%%/', 'Pages\Sets\DeleteSetPage');

        $this->addErrorRoute(ResponseCode::CODE_403, 'Pages\ManagedPage', array('template' => '403.html'));
        $this->addErrorRoute(ResponseCode::CODE_404, 'Pages\ManagedPage', array('template' => '404.html'));
        $this->addErrorRoute(ResponseCode::CODE_500, 'Pages\ManagedPage', array('template' => '500.html'));

        // Acl pages
        $this->addNamedRoute('logout', 'logout/', 'Pages\Acl\LogoutPage');
        $loginRoute = $this->addRoute(null, 'Pages\Acl\LoginPage', null, 0, false);

        if ($this->moduleConfig->aclRule) {
            $this->factory()->acl()->secureUrlRecursive(
                $this->urlPrefix,
                $this->moduleConfig->aclRule,
                $this->moduleConfig->title,
                $loginRoute
            );
        }

        foreach ($this->moduleConfig->plugins as $plugin) {
            $this->importPlugin($plugin['plugin'], $plugin);
        }
    }

    public function initTask(Task $task)
    {
        foreach ($this->plugins as $plugin) {
            $plugin->initTask($task);
        }
    }


    public function beforeParsing(Task $task)
    {
        if ($task->plugin) {
            $task->plugin->beforeParsing($task);
        }
    }

    public function afterParsing(Task $task)
    {
        if ($task->plugin) {
            $task->plugin->afterParsing($task);
        }
    }
}
