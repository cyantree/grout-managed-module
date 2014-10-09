<?php
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Pages\Sets\EditSetPage;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

/** @var $this TemplateContext */

$q = ManagedFactory::get($this->app)->quick();
$ui = ManagedFactory::get($this->app)->ui();

/** @var EditSetPage $page */
$page = $this->task->page;
$set = $page->set;

echo $ui->status($page->status);
echo $ui->status($set->status);
?>

<?= $ui->formStart($page->submitUrl, 'file') ?>
    <div class="item"><label></label>

        <div class="content"><h3><?= $q->e($set->config->get('title')) ?></h3></div>
    </div>
<?php

$content = $set->firstContent;

do {
    if (!$content->config->get('visible')) {
        continue;
    }
    $label = $content->config->get('label');
    ?>
    <div class="item" id="content_<?= $q->e($content->name) ?>">
        <label><?= $q->e($label != '' ? $label . ($content->required ? '*' : '') . ':' : '') ?></label>

        <div class="content">
            <?= $set->render($content) ?>
        </div>
    </div>
    <?php
} while ($content = $content->nextContent);
?>
    <div class="item">
        <label></label>
        <input type="submit" name="save" value="<?=$q->t('Speichern')?>"/>
        <?php
        if ($set->getId() && $set->allowDelete) {
            ?>
            <a href="<?= $q->e($page->deleteUrl) ?>" class="button"><?=$q->t('Löschen')?></a>
            <?php
        }
        ?>
    </div>

<?= $ui->hiddenInput('set_id', $set->getId()) ?>
<?= $ui->formEnd() ?>
