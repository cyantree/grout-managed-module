<?php
use Cyantree\Grout\App\Generators\Template\TemplateContext;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Pages\Sets\DeleteSetPage;

/** @var $this TemplateContext */

$q = ManagedFactory::get($this->app)->quick();
$ui = ManagedFactory::get($this->app)->ui();

/** @var DeleteSetPage $page */
$page = $this->task->page;
$set = $page->set;

echo $ui->status($set->status);

if ($page->deleted) {
    return;
}
?>

<?= $ui->formStart($page->getSubmitUrl(), 'post') ?>
    <div class="item">
        <div class="label">

        </div>
        <div class="content"><h2><?= $q->e($set->config->get('title')) ?></h2></div>
    </div>

    <div class="item">
        <div class="label">

        </div>
        <span><?=$q->t('Soll der Inhalt wirklich gelöscht werden?')?></span>
    </div>

    <div class="item">&nbsp;</div>
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
            <label><?= $label != '' ? $q->e($label) . ':' : '' ?></label>
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
            <input type="submit" name="delete" value="<?=$q->t('Löschen')?>"/>
        </div>
    </div>

<?= $ui->hiddenInput('class', get_class($set)) ?>
<?= $ui->hiddenInput('id', $set->getId()) ?>
<?= $ui->formEnd() ?>
