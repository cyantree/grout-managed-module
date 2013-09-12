<?php
namespace Grout\Cyantree\ManagedModule\Pages;

use Grout\Cyantree\ManagedModule\Forms\LoginForm;
use Grout\Cyantree\ManagedModule\ManagedFactory;

class RestrictedPage extends ManagedPage
{
    public function parseTask()
    {
        if($this->_isAccessible()){
            $this->_onAccessible();
        }else{
            $this->_onInaccessible();
        }
    }

    protected function _isAccessible()
    {
        $f = ManagedFactory::get($this->app);

        return !!$f->appSessionData()->get('userId');
    }

    protected function _onAccessible()
    {
        $template = $this->task->vars->get('template');
        if($template){
            $this->setResult(ManagedFactory::get($this->app)->appTemplates()->load($template), $this->task->vars->get('contentType'), $this->task->vars->get('responseCode'));
        }
    }

    protected function _onInaccessible()
    {
        $f = new LoginForm();
        $f->dataIn = $this->request()->post;
        $f->task = $this->task;
        $f->execute();

        $this->setResult($this->managedFactory()->appTemplates()->load('Cyantree\ManagedModule:login.html', array('form' => $f)));
    }
}