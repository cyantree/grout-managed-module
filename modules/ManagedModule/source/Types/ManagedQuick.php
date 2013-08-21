<?php
namespace Grout\ManagedModule\Types;

use Cyantree\Grout\App\GroutQuick;
use Grout\ManagedModule\ManagedFactory;

class ManagedQuick extends GroutQuick
{
    public $translationDomain = 'default';

    public function t($message, $textDomain = null, $locale = null)
    {
        if($textDomain === null){
            $textDomain = $this->translationDomain;
        }

        return ManagedFactory::get($this->_app)->appTranslator()->translator->translate($message, $textDomain, $locale);
    }
}