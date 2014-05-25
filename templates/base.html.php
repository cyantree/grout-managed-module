<?php
/** @var $this \Cyantree\Grout\App\Generators\Template\TemplateContext */
use Cyantree\Grout\Filter\ArrayFilter;
use Cyantree\Grout\Tools\ArrayTools;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Types\AccessRule;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

$f = ManagedFactory::get($this->app);
$m = $f->module;
$q = $f->appQuick();
$ui = $f->appUi();
$c = $f->appConfig();
?>
<!doctype html>
<html lang="<?=$q->t('de')?>">
<head>
    <title><?=$q->e($c->title)?></title>
    <base href="<?=$q->e($this->task->app->url)?>"/>
    <meta charset="utf-8"/>
    <meta name="generator" content="cyantree grout"/>
    <script src="<?=$q->e($q->a('js/lib.js'))?>"></script>
    <script src="<?=$q->e($q->a('js/app.js'))?>"></script>
    <link rel="stylesheet" href="<?=$q->e($q->a('css/admin.css'))?>"/>
</head>
<body>
<script>
    $.service.url = "<?=$q->e($m->getRouteUrl('service'), 'js')?>";
    $.app.urlPrefix = "<?=$q->e($m->getPublicUrl('', false), 'js')?>";
</script>
<div id="header">
    <p class="title"><a href="<?= $q->e($m->getUrl()) ?>"><?=$q->e($c->title)?></a></p>

    <div class="menu">
        <?php if ($f->appManagedSessionData()->isLoggedIn()) { ?>
            <a href="<?=$q->e($m->getRouteUrl('logout')) ?>"><?=$q->t('Abmelden')?></a>
        <?php } ?>
    </div>
</div>
<div id="page">
    <?php if ($f->appManagedSessionData()->isLoggedIn()) { ?>
        <div id="menu">
            <ul>
                <?php
                $activeMenu = $this->task->vars->get('menu');
                $filter = new ArrayFilter();
                $menuLinks = $m->menuLinks;
                foreach ($menuLinks as $menuLink) {
                    $filter->setData($menuLink);

                    /** @var AccessRule $access */
                    $access = ArrayTools::get($menuLink, 'access');
                    if($access && !$f->hasAccess($access)){
                        continue;
                    }

                    $id = $filter->get('id');
                    $url = $menuLink['url'];
                    $active = ($id !== null && $id === $activeMenu) || $filter->get('route') == $this->task->route;

                    $c = '<li'.($active ? ' class="active"' : '').'><a href="'.$q->e($url).'">'.$q->e($filter->get('title')).'</a></li>';

                    echo $c;
                }?>
            </ul>
        </div>
    <?php } ?>
    <div id="content">
        <?=$this->in->get('content')?>
    </div>
</div>

<div id="layers"></div>
<script>
    $(document).ready(function () {
        $('#layers').CT_Layers();
        var $layers = $('#layers').data('CT_Layers');
        $layers.bind('CT_Layers_LayerCreated', function (e, $layer) {
            $layer.one('CT_Layer_Created', function () {
                $layer.css('left', ($(window).width() - $layer.width()) / 2);
                $layer.css('top', ($(document).scrollTop() + 150) + 'px');
            });

            $layer.bind('CT_Layer_ContentChanged', function () {
                var dif = ($layer.offset().top - $(document).scrollTop());
                if (dif < 0) {
                    $layer.css('top', ($(document).scrollTop() + 50) + 'px');
                } else if (dif > $(window).height() * 0.75) {
                    $layer.css('top', Math.round($(window).height() * 0.75) + 'px');
                }

                $.initContainer($layer);


                $layer.find('div.title span.close').click(function (e) {
                    $layer.hide();
                    return false;
                });

                var $drag = $layer.find('div.title');
                if ($drag.length) $layer.draggable({handle: $drag});
            });
        });

        $.initContainer($(document));
    });
</script>
</body>
</html>