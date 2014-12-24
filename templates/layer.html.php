<?php
use Cyantree\Grout\App\Generators\Template\TemplateContext;
use Grout\Cyantree\ManagedModule\ManagedFactory;

/** @var $this TemplateContext */

$m = $this->task->module;
$q = ManagedFactory::get($this->app, $this->task->module->id)->quick();
?>
<div class="title">
    <span class="title"><?= $q->e($this->in->get('title')) ?></span>
    <span class="close">&times;</span>
</div>
<div class="content">
    <?= $this->in->get('content') ?>
</div>
