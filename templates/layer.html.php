<?php
/** @var $this AppTemplateContext */

use Grout\AppModule\Types\AppTemplateContext;

$m = $this->task->module;
$q = $this->factory()->quick();
?>
<div class="title">
    <span class="title"><?=$q->e($this->in->get('title'))?></span>
    <span class="close">&times;</span>
</div>
<div class="content">
    <?=$this->in->get('content')?>
</div>
