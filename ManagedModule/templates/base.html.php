<?php
/** @var $this \Cyantree\Grout\App\Generators\Template\TemplateContext */
use Cyantree\Grout\App\Route;
use Cyantree\Grout\Filter\ArrayFilter;
use Cyantree\Grout\Tools\ArrayTools;
use Grout\ManagedModule\ManagedFactory;
use Grout\ManagedModule\Types\AccessRule;
use Cyantree\Grout\App\Generators\Template\TemplateContext;
use Grout\BootstrapModule\GlobalFactory;

$f = ManagedFactory::get($this->app);
$m = $f->appModule();
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
    <meta name="generator" content="cyantree web framework"/>
    <script src="<?=$q->e($q->a('assets/js/lib.js'))?>"></script>
    <script src="<?=$q->e($q->a('assets/js/app.js'))?>"></script>
    <link rel="stylesheet" href="<?=$q->e($q->a('assets/css/admin.css'))?>"/>
</head>
<body>
<script>
    $.service.url = "<?=$q->e($m->getRouteUrl('service'), 'js')?>";
    $.app.urlPrefix = "<?=$q->e($m->getPublicUrl('', false), 'js')?>";

    initCallbacks.push(function () {

    });
</script>
<div id="header">
    <h1><a href="<?= $q->e($m->getUrl()) ?>"><?=$q->e($c->title)?></a></h1>

    <div class="menu">
        <?php if ($f->appSessionData()->get('userId')) { ?>
            <a href="<?=$q->e($m->getRouteUrl('logout')) ?>"><?=$q->t('Abmelden')?></a>
        <?php } ?>
    </div>
</div>
<div id="page">
    <?php if ($f->appSessionData()->get('userId')) { ?>
        <div id="menu">
            <ul>
                <?php
                $activeMenu = $this->task->vars->get('menu');
                $filter = new ArrayFilter();
                $menuLinks = $f->appModule()->menuLinks;
                foreach ($menuLinks as $menuLink) {
                    $filter->setData($menuLink);
                    $id = null;
                    $url = null;
                    $type = ArrayTools::get($menuLink, 'type', 'url');
                    $active = false;

                    /** @var AccessRule $access */
                    $access = ArrayTools::get($menuLink, 'access');
                    if($access && !$access->hasAccess($f->appSessionData()->get('userId'), $f->appSessionData()->get('userRole'))){
                        continue;
                    }

                    if($type == 'url'){
                        $id = $filter->get('id');
                        $url = $menuLink['url'];
                    }elseif($type == 'list-sets'){
                        $url = $this->task->module->getRouteUrl('list-sets', array('type' => $menuLink['setType']));
                        $id = $menuLink['setType'].'-sets';
                    }elseif($type == 'edit-set'){
                        $url = $this->task->module->getRouteUrl('edit-set', array('type' => $menuLink['setType'], 'id' => $menuLink['setId']));
                        $id = $menuLink['setType'].'-sets';
                    }elseif($type == 'page'){
                        /** @var Route $page */
                        $page = $menuLink['page'];
                        $url = $page->getUrl($filter->get('arguments'), false);
                        $active = $this->task->route == $page;
                        $id = $menuLink['id'];
                    }

                    $title = $menuLink['title'];
                    $active = $active || ($id !== null && $activeMenu === $id);


                    $c = '<li'.($active ? ' class="active"' : '').'><a href="'.$q->e($url).'">'.$q->e($title).'</a></li>';

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