<?php
/** @var $this TemplateContext */

use Grout\Cyantree\ManagedModule\ManagedFactory;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

$f = ManagedFactory::get($this->app);
$ui = $f->appUi();
$q = $f->appQuick();

echo $ui->statusSuccess($q->t('Sie wurden erfolgreich abgemeldet.'));
?>
<script>
    $.ct.redirect("<?=$q->e($f->module->getUrl(), 'js')?>", 3);
</script>