<?php
/** @var $this \Cyantree\Grout\App\Generators\Template\TemplateContext */
use Cyantree\Grout\Filter\ArrayFilter;
use Grout\Cyantree\AclModule\AclFactory;
use Grout\Cyantree\AclModule\AclModule;
use Grout\Cyantree\AclModule\Tools\AclTool;
use Grout\Cyantree\AclModule\Types\AclRule;
use Grout\Cyantree\ManagedModule\ManagedFactory;

$f = ManagedFactory::get($this->app);
$m = $f->module;
$q = $f->quick();
$ui = $f->ui();
$c = $f->config();
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

    <?php
    if (!$f->acl()->factory()->acl()->getAccount()->role->isGuest) {
        ?><div class="menu">
            <a href="<?= $q->er('logout') ?>"><?= $q->t('Abmelden') ?></a>
        </div>
    <?php
    }
    ?>
</div>
<div id="page">
    <div id="menu">
        <ul>
            <?php
            /** @var AclTool $acl */
            $acl = AclFactory::get()->acl();

            $activeMenu = $this->task->vars->get('menu');
            $filter = new ArrayFilter();
            $menuLinks = $m->menuLinks;
            foreach ($menuLinks as $menuLink) {
                $filter->setData($menuLink);

                /** @var AclRule $access */
                $access = $filter->get('access');

                if ($access) {
                    if (!$acl->satisfies($access)) {
                        continue;
                    }
                }

                $id = $filter->get('id');
                $url = $menuLink['url'];
                $active = ($id !== null && $id === $activeMenu) || $filter->get('route') == $this->task->route;

                $c = '<li' . ($active ? ' class="active"' : '') . '><a href="' . $q->e($url) . '">'
                    . $q->e($filter->get('title')) . '</a></li>';

                echo $c;
            }?>
        </ul>
    </div>
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
