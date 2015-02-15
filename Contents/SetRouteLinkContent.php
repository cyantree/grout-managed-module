<?php
namespace Grout\Cyantree\ManagedModule\Contents;

use Cyantree\Grout\App\Route;
use Cyantree\Grout\Set\Content;

class SetRouteLinkContent extends Content
{
    public $storeInSet = false;
    public $linkLabel;

    /** @var Route */
    public $route;

    public $setIdField;
    public $routeParameters = array();
    public $urlParameters = array();
}
