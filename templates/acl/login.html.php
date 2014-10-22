<?php
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

/** @var $this TemplateContext */

$f = ManagedFactory::get($this->app);

$ui = $f->ui();
$q = $f->quick();

if ($this->task->request->method == 'POST') {
    echo $ui->status($ui->statusError('Sie können diese Seite nicht anzeigen. Bitte prüfen Sie Ihre Zugangsdaten.'));

} else {
    echo $ui->status($ui->statusInfo('Bitte geben Sie Ihre Zugangsdaten ein, um diese Seite anzuzeigen.'));
}
?>

<?= $ui->formStart($this->task->request->url) ?>
<div class="item">
    <div class="label">
        <label></label>
    </div>
    <div class="content"><h3><?= $q->t('Anmelden') ?></h3></div>
</div>
<div class="item">
    <div class="label">
        <label><?= $q->t('Benutzername') ?>:</label>
    </div>
    <div class="content">
        <?= $ui->textInput('username', $this->task->request->post->get('username'), 64) ?>
    </div>
</div>

<div class="item">
    <div class="label">
        <label><?= $q->t('Passwort') ?>:</label>
    </div>
    <div class="content">
        <?= $ui->passwordInput('password', 32) ?>
    </div>
</div>

<div class="item">
    <div class="label">
        <label></label>
    </div>
    <div class="content">
        <input type="submit" name="login" value="<?= $q->t('Anmelden') ?>" />
    </div>
</div>
<?=$ui->formEnd()?>
