<?php
namespace Grout\Cyantree\ManagedModule\Pages\Acl;

use Grout\Cyantree\ManagedModule\Pages\ManagedPage;

class LoginPage extends ManagedPage
{
    public function parseTask()
    {
        $this->setTemplateResult('acl/login.html');
    }
}
