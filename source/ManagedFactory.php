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
        $factory = GroutFactory::getFactory($app, __CLASS__, $moduleId, 'Cyantree\ManagedModule');

        return $factory;
    }

    /** @return TemplateGenerator */
    public function templates()
    {
        if (!($tool = $this->getTool(__FUNCTION__, false))) {
            $tool = new TemplateGenerator();
            $tool->defaultModule = $this->module;
            $tool->app = $this->app;

            $this->setTool(__FUNCTION__, $tool);
        }

        return $tool;
    }

    /** @deprecated */
    public function module()
    {
        if (!($tool = $this->getTool(__FUNCTION__, false))) {
            /** @var ManagedModule $tool */
            $tool = $this->module;

            $this->setTool(__FUNCTION__, $tool);
        }

        return $tool;
    }

    /** @return ManagedQuick */
    public function quick()
    {
        if (!($tool = $this->getTool(__FUNCTION__, false))) {
            $tool = new ManagedQuick($this->app);
            $tool->publicAssetUrl = $this->app->publicUrl . $this->config()->assetUrl;

            $tool->translator = $this->translator();
            $tool->translatorDefaultTextDomain = $this->module->id;

            $this->setTool(__FUNCTION__, $tool);
        }

        return $tool;
    }

    /** @return ManagedConfig */
    public function config()
    {
        if (!($tool = $this->getTool(__FUNCTION__, false))) {
            /** @var ManagedConfig $tool */
            $tool = $this->app->configs->getConfig($this->module->id);

            $this->setTool(__FUNCTION__, $tool);
        }

        return $tool;
    }

    /** @return Translator */
    public function translator()
    {
        if (!($tool = $this->getTool(__FUNCTION__, false))) {
            $tool = new DummyTranslator();

            $this->setTool(__FUNCTION__, $tool);
        }

        return $tool;
    }

    /** @return SetTools */
    public function setTools()
    {
        if (!($tool = $this->getTool(__FUNCTION__, false))) {
            $tool = new SetTools($this);

            $this->setTool(__FUNCTION__, $tool);
        }

        return $tool;
    }

    /** @return MenuTools */
    public function menuTools()
    {
        if (!($tool = $this->getTool(__FUNCTION__, false))) {
            $tool = new MenuTools($this);

            $this->setTool(__FUNCTION__, $tool);
        }

        return $tool;
    }
}
