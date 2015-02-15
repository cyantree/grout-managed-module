<?php
namespace Grout\Cyantree\ManagedModule\Types;

use Grout\Cyantree\AclModule\Types\AclRule;

class ManagedConfig
{
    public $title = 'Restricted area';
    public $aclModuleId = 'CyantreeAclModule';

    /** @var AclRule */
    public $aclRule;

    public $plugins = array();

    // TODO: Configurable context for sets
}
