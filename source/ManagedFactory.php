<?php
namespace Grout\Cyantree\ManagedModule;

use Cyantree\Grout\App\App;
use Cyantree\Grout\App\Generators\Template\TemplateGenerator;
use Cyantree\Grout\App\GroutFactory;
use Cyantree\Grout\Mail\Mail;
use Cyantree\Grout\Translation\DummyTranslator;
use Cyantree\Grout\Translation\Translator;
use Cyantree\Grout\Ui\Ui;
use Grout\AppModule\AppFactory;
use Grout\Cyantree\ManagedModule\Types\ManagedSessionData;
use Grout\Cyantree\ManagedModule\Tools\MenuTools;
use Grout\Cyantree\ManagedModule\Tools\SetTools;
use Grout\Cyantree\ManagedModule\Types\AccessRule;
use Grout\Cyantree\ManagedModule\Types\ManagedConfig;
use Grout\Cyantree\ManagedModule\Types\ManagedQuick;

class ManagedFactory extends AppFactory
{
    /** @var ManagedModule */
    public $module;

    public function __construct()
    {
        parent::__construct();
    }

    /** @return ManagedFactory */
    public static function get(App $app = null, $moduleId = null)
    {
        /** @var ManagedFactory $factory */
        $factory = GroutFactory::_getInstance($app, __CLASS__, $moduleId, 'Cyantree\ManagedModule');

        return $factory;
    }

    /** @return TemplateGenerator */
    public function appTemplates()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        $tool = new TemplateGenerator();
        $tool->defaultModule = $this->module;
        $tool->app = $this->app;

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    /** @deprecated */
    public function appModule()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        /** @var ManagedModule $tool */
        $tool = $this->module;

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    /** @return ManagedQuick */
    public function appQuick()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        $tool = new ManagedQuick($this->app);
        $tool->publicAssetUrl = $this->app->publicUrl . $this->appConfig()->assetUrl;

        $tool->translator = $this->appTranslator();
        $tool->translatorDefaultTextDomain = $this->module->id;

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    /** @return ManagedConfig */
    public function appConfig()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        /** @var ManagedConfig $tool */
        $tool = $this->app->configs->getConfig($this->module->id);

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    /** @return Translator */
    public function appTranslator()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        $event = $this->module->events->trigger('getTranslator');

        if ($event->data) {
            $tool = $event->data;

        } else {
            $tool = new DummyTranslator();
        }

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    /** @return SetTools */
    public function appSetTools()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        $tool = new SetTools($this);

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    /** @return MenuTools */
    public function appMenuTools()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        $tool = new MenuTools($this);

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    /** @return ManagedSessionData */
    public function appManagedSessionData()
    {
        $sessionData = $this->appSessionData();

        $tool = $sessionData->get($this->module->id);

        if ($tool === null) {
            $tool = new ManagedSessionData();

            $sessionData->set($this->module->id, $tool);
        }

        return $tool;
    }

    /** @param $rule AccessRule */
    public function hasAccess($rule)
    {
        $d = $this->appManagedSessionData();
        return $rule->hasAccess($d->userId, $d->userRole);
    }
}