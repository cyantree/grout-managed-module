<?php
/** @var $this \Cyantree\Grout\App\Generators\Template\TemplateContext */
use Cyantree\Grout\AutoLoader;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Forms\LoginForm;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

/** @var LoginForm $f */
$f = $this->in->get('form');

$g = ManagedFactory::get($this->app);

$ui = $g->appUi();
$q = $g->appQuick();

echo $ui->status($f->status);
?>

<?php
if($f->status->success){
    ?>
    <script>
        $.ct.redirect("<?=$q->e($this->task->app->getUrl($this->task->request->url))?>", 3);
    </script>
    <?php
}else{
    ?>
    <?=$ui->formStart($this->task->request->url)?>
    <div class="item"><label></label><div class="content"><h3><?=$q->t('Anmelden')?></h3></div></div>
    <div class="item">
        <label><?=$q->t('Benutzername')?>:</label>
        <div class="content">
            <?=$ui->textInput('username', $f->data->username, 64)?>
        </div>
    </div>

    <div class="item">
        <label><?=$q->t('Passwort')?>:</label>
        <div class="content">
            <?=$ui->passwordInput('password', 32)?>
        </div>
    </div>

    <div class="item">
        <label></label>
        <input type="submit" name="next" value="<?=$q->t('Anmelden')?>" />
    </div>
    <?=$ui->formEnd()?>
    <?php
}?>