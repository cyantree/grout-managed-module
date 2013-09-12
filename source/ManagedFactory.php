<?php
namespace Grout\Cyantree\ManagedModule;

use Cyantree\Grout\App\Generators\Template\TemplateGenerator;
use Cyantree\Grout\App\GroutFactory;
use Cyantree\Grout\Mail\Mail;
use Cyantree\Grout\Ui\Ui;
use Grout\BootstrapModule\GlobalFactory;
use Grout\Cyantree\TranslatorModule\TranslatorModule;
use Grout\Cyantree\ManagedModule\Tools\MenuTools;
use Grout\Cyantree\ManagedModule\Tools\SetTools;
use Grout\Cyantree\ManagedModule\Types\AccessRule;
use Grout\Cyantree\ManagedModule\Types\ManagedConfig;
use Grout\Cyantree\ManagedModule\Types\ManagedQuick;
use Zend\I18n\Translator\Translator;

class ManagedFactory extends GlobalFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    /** @return ManagedFactory */
    public static function get($app)
    {
        /** @var ManagedFactory $factory */
        $factory = GroutFactory::_getInstance($app, __CLASS__);

        return $factory;
    }

    /** @return TemplateGenerator */
    public function appTemplates()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        $tool = new TemplateGenerator();
        $tool->app = $this->app;
        $tool->baseTemplate = 'Cyantree\ManagedModule::base.html';

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    public function appModule()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        /** @var ManagedModule $tool */
        $tool = $this->app->getModuleByType('Cyantree\ManagedModule');

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
        $tool->publicAssetUrl = $this->app->publicUrl.$this->appConfig()->assetUrl;
        $tool->translationDomain = 'Cyantree_ManagedModule';

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    /** @return ManagedConfig */
    public function appConfig()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        /** @var ManagedModule $module */
        $module = $this->app->getModuleById('Cyantree\ManagedModule');
        $tool = $module->moduleConfig;

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    /** @return TranslatorModule */
    public function appTranslator()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        if(!$this->app->hasModule('Cyantree\TranslatorModule')){
            $tool = $this->app->importModule('Cyantree\TranslatorModule');
        }else{
            $tool = $this->app->getModuleByType('Cyantree\TranslatorModule');
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

    /** @param $rule AccessRule */
    public function hasAccess($rule)
    {
        $d = $this->appSessionData();
        return $rule->hasAccess($d->get('userId'), $d->get('userRole'));
    }
}