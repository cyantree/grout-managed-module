<?php
use Cyantree\Grout\Set\Set;
use Grout\ManagedModule\ManagedFactory;
use Grout\ManagedModule\Pages\Sets\DeleteSetPage;
use Cyantree\Grout\App\Generators\Template\TemplateContext;
use Grout\BootstrapModule\GlobalFactory;

/** @var $this TemplateContext */

$q = ManagedFactory::get($this->app)->appQuick();
$ui = ManagedFactory::get($this->app)->appUi();

/** @var DeleteSetPage $page */
$page = $this->task->page;
$set = $page->set;

if($page->status){
    echo $ui->status($page->status);

    if($page->status->success){
        return;
    }
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
    <div class="item">
        <label><?= $label != '' ? $q->e($label) . ':' : '' ?></label>

        <div class="content">
            <?= $set->render(Set::MODE_DELETE, $content) ?>
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