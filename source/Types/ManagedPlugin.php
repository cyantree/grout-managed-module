<?php
namespace Grout\Cyantree\ManagedModule\Types;

use Cyantree\Grout\App\Plugin;
use Grout\Cyantree\ManagedModule\ManagedFactory;

class ManagedPlugin extends Plugin
{
    public $extendsService = false;

    /** @var ManagedFactory */
    private $_factory;

    /** @return ManagedFactory */
    public function factory()
    {
        if (!$this->_factory) {
            $this->_factory = ManagedFactory::get($this->app);
        }

        return $this->_factory;
    }
}