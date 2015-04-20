<?php
namespace Grout\Cyantree\ManagedModule\Pages;

use Cyantree\Grout\App\Page;
use Cyantree\Grout\App\Types\ContentType;
use Cyantree\Grout\Filter\ArrayFilter;
use Grout\Cyantree\ManagedModule\ManagedFactory;

class ManagedPage extends Page
{
    /** @var ManagedFactory */
    private $factory;

    public $baseTemplate = '.Cyantree\ManagedModule::base.html';

    /** @return ManagedFactory */
    public function factory()
    {
        if (!$this->factory) {
            $this->factory = ManagedFactory::get($this->app, $this->task->module->id);
        }

        return $this->factory;
    }

    public function parseTask()
    {
        $this->setTemplateResult($this->task->vars->get('template'), $this->task->vars->get('templateData'), array(
                'baseTemplate' => $this->task->vars->get('baseTemplate'),
                'contentType' => $this->task->vars->get('contentType'),
                'responseCode' => $this->task->vars->get('responseCode')
        ));
    }

    public function setTemplateResult($template, $templateData = null, $settings = null)
    {
        $settings = new ArrayFilter($settings);

        $content = $this->factory()->templates()->load($template, $templateData)->content;

        $baseTemplate = $settings->get('baseTemplate');
        if (!$baseTemplate && $baseTemplate !== false) {
            $baseTemplate = $this->baseTemplate;
        }

        if ($baseTemplate) {
            $content = $this->factory()->templates()->load($baseTemplate, array('content' => $content))->content;
        }

        $this->setResult($content, $settings->get('contentType'), $settings->get('responseCode'));
    }
}
