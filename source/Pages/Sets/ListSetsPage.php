<?php
namespace Grout\Cyantree\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Set\Set;
use Cyantree\Grout\Set\SetListResult;
use Cyantree\Grout\StatusContainer;
use Grout\Cyantree\ManagedModule\Pages\RestrictedPage;

class ListSetsPage extends RestrictedPage
{
    public $type;

    public $search;
    public $page;
    public $entitiesPerPage;
    public $countPages;

    public $sortBy;
    public $sortDirection;

    /** @var StatusContainer */
    public $status;

    public $pageUrl;

    /** @var Set */
    public $set;

    /** @var SetListResult */
    public $sets;

    public $searchAvailable = true;

    public $template = 'CyantreeManagedModule::sets/list.html';

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
        $this->status = new StatusContainer();

        $this->task->vars->set('menu', $type.'-sets');

        $this->init();
        $this->_prepare();

        if ($this->task->request->method == 'POST') {
            $this->_onSubmit();
        }

        $this->_loadSets();
        $this->_prepareRendering();

        if ($this->renderPage()) {
            $this->setResult($this->factory()->appTemplates()->load($this->template));
        }
    }

    public function getUrlArguments($context)
    {
        $data = array(
            'sortBy' => $this->sortBy != '' ? $this->sortBy : null,
            'sortDirection' => $this->sortDirection == 'desc' ? null : 'desc',
            'search' => $this->search != '' ? $this->search : null
        );

        if ($context != 'filter') {
            $data['page'] = $this->page > 1 ? $this->page : null;
        }

        return $data;
    }

    protected function _prepareRendering()
    {

    }

    public function renderNavigationBarContent()
    {
        return $this->renderSearchInput().$this->renderPagination().$this->renderNavigationBarRightContent($this->renderExportButton().$this->renderAddButton());
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
        $this->pageUrl = $this->task->module->getRouteUrl('list-sets', array('type' => $this->type)).'?';

        // Prepare rendering
        $this->set->prepareRendering(Set::MODE_LIST);

        if(!$this->entitiesPerPage){
            $this->entitiesPerPage = $this->set->config->asFilter('ListPage')->get('setsPerPage', 10);
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
        $this->countPages = $this->entitiesPerPage ? ceil($this->sets->countAll / $this->entitiesPerPage) : 1;
    }

    public function getEditUrl($id, $type = null)
    {
        if(!$type){
            $type = $this->type;
        }

        return $this->task->module->getRouteUrl('edit-set', array('type' => $type, 'id' => $id));
    }

    public function getAddUrl($type = null)
    {
        if(!$type){
            $type = $this->type;
        }

        return $this->task->module->getRouteUrl('add-set', array('type' => $type));
    }

    public function getExportUrl($type = null, $parameters = null)
    {
        if(!$type){
            $type = $this->type;
        }

        return $this->task->module->getRouteUrl('export-sets', array('type' => $type), true, $parameters);
    }

    public function getDeleteUrl($id, $type = null)
    {
        if(!$type){
            $type = $this->type;
        }

        return $this->task->module->getRouteUrl('delete-set', array('type' => $type, 'id' => $id));
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

    public function renderPage()
    {
        return true;
    }

    public function renderScripts()
    {
        return '';
    }

    public function renderReadyScripts()
    {
        return '';
    }

    public function renderBelowHeader()
    {
        return '';
    }

    public function renderAboveList()
    {
        return '';
    }

    public function renderBelowList()
    {
        return '';
    }

    public function renderFooter()
    {
        return '';
    }

    public function renderTable()
    {
        $q = $this->factory()->appQuick();
        $u = $this->factory()->appUi();

        $globalDelete = $this->set->allowDelete;
        $globalEdit = $this->set->allowEdit;

        $table = '<table><thead><tr>';

        $capabilities = $this->set->getCapabilities();

        $content = $this->set->firstContent;
        do{
            if($content->config->get('visible')){
                $c = $content->config->get('label');
                if ($content->config->get('escapeLabel', true)) {
                    $c = $q->e($c);
                }
                if($capabilities->sort && $content->sortable){
                    $arguments = $this->getUrlArguments('sort');
                    $arguments['sortBy'] = $content->name;

                    if($this->sortBy == $content->name && $this->sortDirection == 'desc'){
                        $arguments['sortDirection'] = 'asc';
                    }else{
                        $arguments['sortDirection'] = 'desc';
                    }

                    $url = $this->pageUrl.$this->encodeArgs($arguments);

                    $c = '<a href="'.$q->e($url).'">'.$c.'</a>';
                }
                $table .= '<td>'.$c.'</td>';
            }
        }while($content = $content->nextContent);

        if($globalEdit){
            $table .= '<td>'.$q->t('Bearbeiten').'</td>';
        }
        if($globalDelete){
            $table .= '<td>'.$q->t('Löschen').'</td>';
        }

        $table .= '</tr></thead><tbody>';

        while ($set = $this->sets->getNext()) {
            $this->_onRenderTableSetChanged();

            $content = $this->set->firstContent;

            $table .= '<tr>';
            do{
                if($content->config->get('visible')){
                    $table .= '<td>'.$content->render('list');
                }
            }while($content = $content->nextContent);

            if ($globalEdit) {
                if ($this->set->allowEdit) {
                    $table .= '<td>'.$u->link($this->getEditUrl($this->set->getId()), $q->t('Bearbeiten')).'</td>';
                } else {
                    $table .= '<td></td>';
                }
            }

            if ($globalDelete) {
                if ($this->set->allowDelete) {
                    $table .= '<td>'.$u->link($this->getDeleteUrl($this->set->getId()), $q->t('Löschen')).'</td>';
                } else {
                    $table .= '<td></td>';
                }
            }

            $table .= '</tr>';
        }

        $table .= '</tbody></table>';

        return $table;
    }

    public function renderFormStart()
    {
        $u = $this->factory()->appUi();

        return $u->formStart($this->app->getUrl($this->task->request->url, true, $this->getUrlArguments('form')), 'post');
    }

    public function renderFormEnd()
    {
        $u = $this->factory()->appUi();

        return $u->formEnd();
    }

    public function renderNavigationBar()
    {
        return '<div class="container">'.$this->renderNavigationBarContent().'</div>';
    }

    public function renderNavigationBarRightContent($content)
    {
        return '<div class="absoluteRight">' . $content . '</div>';
    }

    public function renderSearchInput()
    {
        if (!$this->searchAvailable) {
            return '';
        }

        $q = $this->factory()->appQuick();

        return '<input type="text" placeholder="'.$q->e($q->t('Suche')).'" name="search" class="updateOnChange" data-update-on-change-ignore-args="page" value="'.$q->e($this->search).'" />';
    }

    public function renderPagination()
    {
        if (!$this->set->getCapabilities()->pagination) {
            return '';
        }

        $u = $this->factory()->appUi();

        $pagerArgs = $this->getUrlArguments('pagination');
        $pagerArgs['page'] = '__page__';

        return $u->pageSelector($u->calculatePageSelector($this->countPages, $this->page, 3, 3), $this->pageUrl.$this->encodeArgs($pagerArgs), array('pagePlaceholder' => '__page__'));
    }

    public function renderAddButton()
    {
        $q = $this->factory()->appQuick();
        $u = $this->factory()->appUi();

        if($this->set->allowAdd){
            $class = 'button';
            return $u->link($this->getAddUrl(), $q->t('Hinzufügen'), '_self', array('class' => $class));
        }

        return '';
    }

    public function renderExportButton()
    {
        $q = $this->factory()->appQuick();
        $u = $this->factory()->appUi();

        if($this->set->allowExport){
            $class = 'button';
            return $u->link($this->getExportUrl(), $q->t('Exportieren'), '_self', array('class' => $class));
        }

        return '';
    }

    public function renderHeader()
    {
        $q = $this->factory()->appQuick();

        $c = '<h2>'.$q->e($this->set->config->get('title')).'</h2>';
        if($description = $this->set->config->get('description')){
            $c .= '<p>'.$q->e($description).'</p>';
        }
        $c .= '<hr />';

        return $c;
    }
}