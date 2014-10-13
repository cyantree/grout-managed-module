<?php
namespace Grout\Cyantree\ManagedModule\Types;

use Grout\Cyantree\AclModule\Types\AclRule;

class RegisterSetConfig
{
    public $listPage;

    public $editPage;
    public $addPage;
    public $deletePage;

    /** @var AclRule */
    public $listPageAccess;

    /** @var AclRule */
    public $exportAccess;

    /** @var AclRule */
    public $editPageAccess;

    /** @var AclRule */
    public $addPageAccess;

    /** @var AclRule */
    public $deletePageAccess;

    public function setAllAccessRules(AclRule $rule)
    {
        $this->listPageAccess = $this->exportAccess = $this->editPageAccess = $this->addPageAccess = $this->deletePageAccess = $rule;
    }
}