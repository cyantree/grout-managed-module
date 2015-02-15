<?php
namespace Grout\Cyantree\ManagedModule\Types;

use Cyantree\Grout\App\Plugin;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\ManagedModule;

class ManagedPlugin extends Plugin
{
    public $extendsService = false;

    /** @var ManagedModule */
    public $module;

    /** @var ManagedFactory */
    private $factory;

    /** @return ManagedFactory */
    public function factory()
    {
        if (!$this->factory) {
            $this->factory = ManagedFactory::get($this->app);
        }

        return $this->factory;
    }
}
