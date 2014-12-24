<?php
namespace Grout\Cyantree\ManagedModule\Pages\Acl;

use Grout\Cyantree\ManagedModule\Pages\ManagedPage;

class LogoutPage extends ManagedPage
{
    public function parseTask()
    {
        $this->factory()->acl()->factory()->sessionData()->logout();

        $this->setTemplateResult('acl/logout.html');
    }
}
