<?php
/** @var $this TemplateContext */
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Pages\Sets\ListSetsPage;
use Cyantree\Grout\App\Generators\Template\TemplateContext;

/** @var $page ListSetsPage */
$page = $this->task->page;

$q = ManagedFactory::get($this->app)->appQuick();
?>

<script>
    var listArgs = <?=json_encode($page->getUrlArguments('javascript'))?>;
    getListUrl = function(obj){
        obj = $.extend(listArgs, obj);

        var query = "";
        $.each(obj, function(key, value){
            if (value !== "" && value !== null) {
                query += "&" + key + "=" + encodeURIComponent(value);
            }
        });
        return '<?=$q->e($page->pageUrl, 'js')?>' + query;
    };

    <?=$this->in->get('scripts')?>
    $(document).ready(function(){
        var updateWithChange = function($el) {
            var o = {};
            o[$el.prop('name')] = $el.val();

            window.location.href = getListUrl(o);
        };
        $('select.updateOnChange').change(function(){
            updateWithChange($(this));
        });
        $('input.updateOnChange[type="text"]').keydown(function(e){
            if(e.which == 13){
                updateWithChange($(this));
            }
        });
        <?=$this->in->get('readyScripts')?>
    });
</script>
<?php
echo $this->in->get('rightToSearch');

echo $page->renderHeader();
echo $this->in->get('belowHeader');
echo $page->renderNavBar($this->in->get('navBar'));
echo $this->in->get('aboveList');
echo $page->renderTable();
echo $this->in->get('belowList');
echo $page->renderNavBar($this->in->get('navBar'));
echo $this->in->get('footer');
echo $page->renderFooter();