<?php
namespace Grout\Cyantree\ManagedModule\Pages;


class LogoutPage extends RestrictedPage
{
    protected function _onAccessible()
    {
        $this->module->events->trigger('logout');

        $this->factory()->appManagedSessionData()->reset();

        $this->setResult($this->factory()->appTemplates()->load('logout.html'));
    }
}