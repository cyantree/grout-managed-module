<?php
namespace Grout\Cyantree\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Csv\CsvWriter;
use Cyantree\Grout\Set\Set;
use Cyantree\Grout\Set\SetListResult;
use Grout\Cyantree\ManagedModule\Pages\RestrictedPage;

class ExportSetsPage extends RestrictedPage
{
    public $type;

    public $search;
    public $page;
    public $entitiesPerPage;

    public $sortBy;
    public $sortDirection;

    /** @var Set */
    public $set;

    /** @var SetListResult */
    public $sets;

    public $searchAvailable = true;

    protected function _onAccessible()
    {
        $type = $this->task->vars->get('type');

        if (!$this->factory()->module->setTypes->has($type)) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $setClass = $this->factory()->module->setTypes->get($type);

        // Is no valid set type
        if(!$setClass){
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        // Retrieve current set class
        $this->set = new $setClass();

        if (!$this->set->allowList) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->type = $type;

        $this->init();
        $this->_prepare();

        $this->_loadSets();

        $this->_generateExport();
    }

    protected function _generateExport()
    {
        $csv = new CsvWriter();
        $csv->open();

        $content = $this->set->firstContent;

        $fields = array();
        do {
            if ($content->config->get('visible')) {
                $fields[] = $content->config->get('label');
            }

        } while($content = $content->nextContent);

        $csv->append($fields);

        while ($set = $this->sets->getNext()) {
            $fields = array();

            $this->_onRenderSetChanged();

            $content = $this->set->firstContent;

            do{
                if($content->config->get('visible')){
                    $fields[] = $content->render(Set::MODE_EXPORT);
                }
            }while($content = $content->nextContent);

            $csv->append($fields);
        }

        $data = chr(239) . chr(187) . chr(191) . $csv->getContents();
        $csv->close();

        $res = $this->response();
        $res->asDownload($this->_getExportFilename());
        $res->postContent($data);
    }

    protected function _onRenderSetChanged()
    {

    }

    protected function _getExportFilename()
    {
        return $this->type . '.csv';
    }

    protected function _prepare()
    {
        $f = $this->task->request->get;
        $this->page = $f->asInt('page')->limit(1, 999999)->value;
        $this->search = $f->asString('search')->asInput(64)->value;
        $this->sortBy = $f->asString('sortBy')->asInput(64)->value;
        $this->sortDirection = $f->asList('sortDirection')->match(array('asc', 'desc'), 'desc')->value;
    }

    public function init()
    {
        // Prepare rendering
        $this->set->prepareRendering(Set::MODE_EXPORT);

        if(!$this->entitiesPerPage){
            $this->entitiesPerPage = $this->set->config->asFilter('ListPage')->get('setsPerPage', 0);
        }

        // Check whether search is available
        if($this->set->getCapabilities()->search && $this->searchAvailable){
            $searchable = false;
            foreach($this->set->contents as $content){
                if($content->searchable){
                    $searchable = true;
                    break;
                }
            }
            if($this->searchAvailable && !$searchable){
                $this->searchAvailable = false;
            }

        } else {
            $this->searchAvailable = false;
        }
    }

    protected function _prepareLoadSetsOptions()
    {
        return array(
            'offset' => $this->entitiesPerPage ? ($this->page - 1) * $this->entitiesPerPage : 0,
            'count' => $this->entitiesPerPage,
            'search' => $this->search,
            'sort' => array('field' => $this->sortBy, 'direction' => $this->sortDirection)
        );
    }

    protected function _loadSets()
    {
        $this->sets = $this->set->listSets($this->_prepareLoadSetsOptions());
    }

    protected function _onRenderTableSetChanged()
    {

    }

    public function encodeArgs($args)
    {
        $s = '';

        foreach ($args as $key => $value) {
            if($value === null || $value === '') {
                continue;
            }

            if ($s !== '') {
                $s .= '&';
            }

            $s .= $key . '=' . rawurlencode($value);
        }

        return $s;
    }

    protected function _onSubmit()
    {

    }
}