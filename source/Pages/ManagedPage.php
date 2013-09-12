<?php
namespace Grout\Cyantree\ManagedModule\Pages;

use Cyantree\Grout\App\Page;
use Cyantree\Grout\App\Types\ContentType;
use Cyantree\Grout\App\Types\ResponseCode;
use Grout\BootstrapModule\GlobalFactory;
use Grout\Cyantree\ManagedModule\ManagedFactory;

class ManagedPage extends Page
{

    public function globalFactory()
    {
        return GlobalFactory::get($this->app);
    }

    public function managedFactory()
    {
        return ManagedFactory::get($this->app);
    }

    public function parseError($code, $data = null)
    {
        if($code == ResponseCode::CODE_404){
            $this->setResult($this->managedFactory()->appTemplates()->load('Cyantree\ManagedModule:404.html'), ContentType::TYPE_HTML_UTF8, ResponseCode::CODE_404);
        }else{
            $this->setResult($this->managedFactory()->appTemplates()->load('Cyantree\ManagedModule:500.html'), ContentType::TYPE_HTML_UTF8, ResponseCode::CODE_500);
        }
        parent::parseError($code, $data);
    }
}