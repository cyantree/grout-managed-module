<?php
/** @var $this TemplateContext */
use Grout\ManagedModule\ManagedFactory;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

$q = ManagedFactory::get($this->app)->appQuick();
?>

<?=$q->t('Fehler 404 - Die Seite wurde nicht gefunden')?>