<?php
use Cyantree\Grout\App\Generators\Template\TemplateContext;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Pages\Sets\ListSetsPage;

/** @var $this TemplateContext */

/** @var $page ListSetsPage */
$page = $this->task->page;

$q = ManagedFactory::get($this->app)->appQuick();
$ui = ManagedFactory::get($this->app)->appUi();
?>

    <script>
        var listArgs = <?=json_encode($page->getUrlArguments('filter'))?>;
        getListUrl = function(obj){
            var query = "";
            $.each(obj, function(key, value){
                if (value !== "" && value !== null) {
                    if (query !== "") {
                        query += "&";
                    }

                    query += key + "=" + encodeURIComponent(value);
                }
            });
            return '<?=$q->e($page->pageUrl, 'js')?>' + query;
        };

        <?=$page->renderScripts()?>
        $(document).ready(function(){
            var updateWithChange = function($el) {
                var o = {};
                o[$el.prop('name')] = $el.val();

                o = $.extend(listArgs, o);

                var ignoreArgs = $el.data('update-on-change-ignore-args');
                if (ignoreArgs) {
                    ignoreArgs = ignoreArgs.split(",");

                    $.each(ignoreArgs, function() {
                        delete(o[this]);
                    });
                }

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
            <?=$page->renderReadyScripts()?>

            if ($('div.GroutStatusBox .success')) {
                setTimeout(function() {
                    $('div.GroutStatusBox .success').fadeOut();
                }, 5000);
            }
        });
    </script>
<?php
echo $page->renderHeader();
echo $ui->status($page->status);
echo $page->renderFormStart();
echo $page->renderNavigationBar();
echo $page->renderAboveList();
echo $page->renderTable();
echo $page->renderBelowList();
echo $page->renderNavigationBar();
echo $page->renderFormEnd();
echo $page->renderFooter();