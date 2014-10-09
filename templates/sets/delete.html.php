<?php

/** @var $this TemplateContext */

use Cyantree\Grout\App\Generators\Template\TemplateContext;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Pages\Sets\DeleteSetPage;

$q = ManagedFactory::get($this->app)->quick();
$ui = ManagedFactory::get($this->app)->ui();

/** @var DeleteSetPage $page */
$page = $this->task->page;
$set = $page->set;

echo $ui->status($page->status);
echo $ui->status($set->status);

if ($page->deleted) {
    return;
}
?>

<?= $ui->formStart($page->submitUrl, 'post') ?>
    <div class="item"><label></label>

        <div class="content"><h3><?= $q->e($set->config->get('title')) ?></h3></div>
    </div>

    <div class="item">
        <label></label>
        <span><?=$q->t('Soll der Inhalt wirklich gelöscht werden?')?></span>
    </div>

    <div class="item">&nbsp;</div>
<?php
$content = $set->firstContent;
do {
    if (!$content->config->get('visible')) {
        continue;
    }

    $label = $content->config->get('label');
    ?>
    <div class="item" id="content_<?= $q->e($content->name) ?>">
        <label><?= $label != '' ? $q->e($label) . ':' : '' ?></label>

        <div class="content">
            <?= $set->render($content) ?>
        </div>
    </div>
    <?php
} while ($content = $content->nextContent);
?>
    <div class="item">
        <label></label>
        <input type="submit" name="delete" value="<?=$q->t('Löschen')?>"/>
    </div>

<?= $ui->hiddenInput('class', get_class($set)) ?>
<?= $ui->hiddenInput('id', $set->getId()) ?>
<?= $ui->formEnd() ?>
