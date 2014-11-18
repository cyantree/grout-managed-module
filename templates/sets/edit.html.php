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

echo $ui->status($set->status);
?>

<?= $ui->formStart($page->submitUrl, 'file') ?>
    <div class="item">
        <div class="label">

        </div>
        <div class="content"><h2><?= $q->e($set->config->get('title')) ?></h2></div>
    </div>
<?php

$content = $set->firstContent;

do {
    if (!$content->enabled || !$content->render) {
        continue;
    }

    $label = $content->config->get('label');
    ?>
    <div class="item" id="content_<?= $q->e($content->name) ?>">
        <div class="label">
            <label><?= $q->e($label != '' ? $label . ($content->required ? '*' : '') . ':' : '') ?></label>
            <?php
            if ($content->config->get('note')) {
                ?>
                <p><?= $q->e($content->config->get('note')) ?></p>
            <?php
            }
            ?>
        </div>

        <div class="content">
            <?= $set->render($content) ?>
        </div>
    </div>
    <?php
} while ($content = $content->nextContent);
?>
    <div class="item">
        <div class="label">

        </div>
        <div class="content">
            <input type="submit" name="save" value="<?=$q->t('Speichern')?>"/>

            <?php
            if (!$set->getId()) {
                ?>
                <input type="submit" name="saveAndNew" value="<?=$q->t('Speichern und weiter')?>"/>
                <?php
            }
            ?>

            <?php
            if ($set->getId() && $set->allowDelete) {
                ?>
                <a href="<?= $q->e($page->deleteUrl) ?>" class="button"><?=$q->t('Löschen')?></a>
            <?php
            }
            ?>
        </div>
    </div>

<?= $ui->hiddenInput('set_id', $set->getId()) ?>
<?= $ui->formEnd() ?>
