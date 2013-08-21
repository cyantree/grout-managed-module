<?php
/** @var $this TemplateContext */

use Grout\ManagedModule\ManagedFactory;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

$ui = ManagedFactory::get($this->app)->appUi();
$q = ManagedFactory::get($this->app)->appQuick();

echo $ui->statusSuccess($q->t('Sie wurden erfolgreich abgemeldet.'));