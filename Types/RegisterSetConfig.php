<?php
namespace Grout\Cyantree\ManagedModule\Types;

use Grout\Cyantree\AclModule\Types\AclRule;

class RegisterSetConfig
{
    public $listPage;

    public $editPage;
    public $addPage;
    public $deletePage;
    public $exportPage;

    /** @var AclRule */
    public $listPageAccess;

    /** @var AclRule */
    public $exportPageAccess;

    /** @var AclRule */
    public $editPageAccess;

    /** @var AclRule */
    public $addPageAccess;

    /** @var AclRule */
    public $deletePageAccess;

    public function setAllAccessRules(AclRule $rule)
    {
        $this->listPageAccess = $this->exportPageAccess = $this->editPageAccess = $this->addPageAccess = $this->deletePageAccess = $rule;
    }
}
