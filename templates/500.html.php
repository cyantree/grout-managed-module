<?php
/** @var $this TemplateContext */
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

$q = ManagedFactory::get($this->app)->quick();
?>

<?= $q->t('Fehler 500 - Es ist ein unbekannter Fehler aufgetreten.') ?>
