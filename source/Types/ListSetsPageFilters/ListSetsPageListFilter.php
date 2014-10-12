<?php
namespace Grout\Cyantree\ManagedModule\Types\ListSetsPageFilters;

use Cyantree\Grout\Filter\ArrayFilter;

class ListSetsPageListFilter extends ListSetsPageFilter
{
    public $options = array();

    public function readValue(ArrayFilter $data)
    {
        $this->value = $data->asList($this->name)->match(array_keys($this->options), $this->defaultValue)->value;
    }

    public function render()
    {
        return $this->factory->ui()->select($this->name, $this->options, $this->value, array('class' => 'updateOnChange'));
    }
}
