<?php
namespace Grout\ManagedModule;

use Cyantree\Grout\App\Generators\Template\TemplateGenerator;
use Cyantree\Grout\App\GroutFactory;
use Cyantree\Grout\Filter\ArrayFilter;
use Cyantree\Grout\Mail\Mail;
use Cyantree\Grout\Ui\Ui;
use Grout\BootstrapModule\GlobalFactory;
use Grout\Cyantree\TranslatorModule\TranslatorModule;
use Grout\ManagedModule\Types\AccessRule;
use Grout\ManagedModule\Types\ManagedConfig;
use Grout\ManagedModule\Types\ManagedQuick;
use Zend\Cache\Storage\Adapter\Filesystem;
use Zend\Cache\Storage\Adapter\FilesystemOptions;
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
        $tool->baseTemplate = 'ManagedModule::base.html';

        $this->_setAppTool(__FUNCTION__, $tool);
        return $tool;
    }

    public function appModule()
    {
        if($tool = $this->_getAppTool(__FUNCTION__, __CLASS__)){
            return $tool;
        }

        /** @var ManagedModule $tool */
        $tool = $this->app->getModuleByType('ManagedModule');

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
        $tool->translationDomain = 'ManagedModule';

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
        $module = $this->app->getModuleById('ManagedModule');
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

    /** @param $rule AccessRule */
    public function hasAccess($rule)
    {
        $d = $this->appSessionData();
        return $rule->hasAccess($d->get('userId'), $d->get('userRole'));
    }
}