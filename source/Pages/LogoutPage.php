<?php
namespace Grout\ManagedModule\Pages;


class LogoutPage extends RestrictedPage
{
    protected function _onAccessible()
    {
        $this->managedFactory()->appSessionData()->delete(array('userRole', 'userId'));

        $this->setResult($this->managedFactory()->appTemplates()->load('logout.html'));
    }
}