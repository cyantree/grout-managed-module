<?php
namespace Grout\Cyantree\ManagedModule\Pages;


class LogoutPage extends RestrictedPage
{
    protected function _onAccessible()
    {
        $this->module->events->trigger('logout');

        $this->factory()->managedSessionData()->reset();

        $this->setResult($this->factory()->templates()->load('logout.html'));
    }
}