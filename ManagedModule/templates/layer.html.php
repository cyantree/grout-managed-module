<?php
/** @var $this TemplateContext */

use Cyantree\Grout\App\Generators\Template\TemplateContext;
use Grout\BootstrapModule\GlobalFactory;

$m = $this->task->module;
$q = GlobalFactory::get($this->app)->appQuick();
?>
<div class="title">
    <span class="title"><?=$q->e($this->in->get('title'))?></span>
    <span class="close">&times;</span>
</div>
<div class="content">
    <?=$this->in->get('content')?>
</div>
