<?php
namespace Grout\Cyantree\ManagedModule\Types\ListSetsPageFilters;

use Cyantree\Grout\Filter\ArrayFilter;
use Grout\Cyantree\ManagedModule\ManagedFactory;

abstract class ListSetsPageFilter
{
    public $name;

    public $defaultValue;

    public $value;

    /** @var ManagedFactory */
    protected $factory;

    abstract public function render();

    public function readValue(ArrayFilter $data)
    {
        $this->value = $data->get($this->name, $this->defaultValue);
    }

    public function getStringValue()
    {
        return $this->value;
    }

    public function init(ManagedFactory $factory)
    {
        $this->factory = $factory;
    }
}
