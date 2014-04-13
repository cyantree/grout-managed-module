<?php
namespace Grout\Cyantree\ManagedModule\Types;

use Cyantree\Grout\App\GroutQuick;
use Grout\AppModule\Helpers\Translation\Translator;

class ManagedQuick extends GroutQuick
{
    /** @var Translator */
    public $translator;
    public $translatorDefaultTextDomain = 'default';

    public function t($message, $textDomain = null, $locale = null)
    {
        if ($textDomain === null) {
            $textDomain = $this->translatorDefaultTextDomain;
        }

        return $this->translator->translate($message, $textDomain, $locale);
    }
}