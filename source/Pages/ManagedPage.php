<?php
namespace Grout\Cyantree\ManagedModule\Pages;

use Cyantree\Grout\App\Page;
use Cyantree\Grout\App\Types\ContentType;
use Cyantree\Grout\App\Types\ResponseCode;
use Grout\Cyantree\ManagedModule\ManagedFactory;

class ManagedPage extends Page
{
    /** @var ManagedFactory */
    private $_factory;

    /** @return ManagedFactory */
    public function factory()
    {
        if (!$this->_factory) {
            $this->_factory = ManagedFactory::get($this->app, $this->task->module->id);
        }

        return $this->_factory;
    }

    public function parseError($code, $data = null)
    {
        if($code == ResponseCode::CODE_404){
            $this->setResult($this->factory()->templates()->load('CyantreeManagedModule:404.html'), ContentType::TYPE_HTML_UTF8, ResponseCode::CODE_404);
        }else{
            $this->setResult($this->factory()->templates()->load('CyantreeManagedModule:500.html'), ContentType::TYPE_HTML_UTF8, ResponseCode::CODE_500);
        }
        parent::parseError($code, $data);
    }

    public function setResult($content, $contentType = null, $responseCode = null, $baseTemplate = null)
    {
        if ($baseTemplate !== false) {
            $content = $this->factory()->templates()->load($baseTemplate === null ? 'CyantreeManagedModule:base.html' : $baseTemplate, array('content' => $content), false)->content;
        }

        parent::setResult($content, $contentType, $responseCode);
    }
}