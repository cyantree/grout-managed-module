<?php
namespace Grout\Cyantree\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Csv\CsvWriter;
use Cyantree\Grout\Set\Set;
use Cyantree\Grout\Set\SetListResult;
use Grout\Cyantree\ManagedModule\Pages\ManagedPage;
use Grout\Cyantree\ManagedModule\Types\ListSetsPageFilters\ListSetsPageListFilter;

class ListSetsPage extends ManagedPage
{
    public $type;

    public $search;
    public $page;
    public $entitiesPerPage;
    public $countPages;

    public $sortBy;
    public $sortDirection;

    public $pageUrl;

    /** @var Set */
    public $set;

    /** @var SetListResult */
    public $sets;

    public $searchAvailable = true;

    public $template = 'CyantreeManagedModule::sets/list.html';

    public $format;
    public $mode;

    /** @var ListSetsPageListFilter[] */
    private $filters = array();

    public function parseTask()
    {
        $type = $this->task->vars->get('type');

        if (!$this->factory()->module->setTypes->has($type)) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $setClass = $this->factory()->module->setTypes->get($type);

        // Is no valid set type
        if (!$setClass) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        // Retrieve current set class
        $this->set = new $setClass();
        $this->set->status->setTranslator($this->factory()->translator());
        $this->set->config->setAsFilter('ListPage', array('setsPerPage' => 20));
        
        $acl = $this->factory()->acl()->factory()->acl();
        $setConfig = $this->factory()->setTools()->getConfig($type);
        $this->set->allowAdd = $setConfig->addPageAccess ? $acl->satisfies($setConfig->addPageAccess) : true;
        $this->set->allowEdit = $setConfig->editPageAccess ? $acl->satisfies($setConfig->editPageAccess) : true;
        $this->set->allowDelete = $setConfig->deletePageAccess ? $acl->satisfies($setConfig->deletePageAccess) : true;
        $this->set->allowExport = $setConfig->exportAccess ? $acl->satisfies($setConfig->exportAccess) : true;
        $this->set->allowList = $setConfig->listPageAccess ? $acl->satisfies($setConfig->listPageAccess) : true;

        if ($this->task->vars->get('mode') == 'export') {
            $this->format = Set::FORMAT_PLAIN;
            $this->mode = Set::MODE_EXPORT;

        } else {
            $this->format = Set::FORMAT_HTML;
            $this->mode = Set::MODE_LIST;
        }

        $this->set->init($this->mode, $this->format, $this->module->id . ':' . $this->module->type);

        if (!$this->set->allowList) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->type = $type;

        $this->task->vars->set('menu', $type . '-sets');

        $this->init();
        $this->prepare();

        if ($this->mode == Set::MODE_EXPORT) {
            $this->entitiesPerPage = 0;
            $this->loadSets();
            $this->generateExport($this->task->vars->get('format'));
            return;
        }

        if ($this->task->request->method == 'POST') {
            $this->onSubmit();
        }

        $this->loadSets();
        $this->prepareRendering();

        if ($this->renderPage()) {
            $this->setResult($this->factory()->templates()->load($this->template));
        }
    }

    public function addFilter(ListSetsPageListFilter $filter)
    {
        $filter->init($this->factory());
        $this->filters[] = $filter;
    }

    public function getUrlArguments($context)
    {
        $data = array(
            'sortBy' => $this->sortBy != '' ? $this->sortBy : null,
            'sortDirection' => $this->sortDirection,
            'search' => $this->search != '' ? $this->search : null
        );

        if ($context != 'filter' && $context != 'export') {
            $data['page'] = $this->page > 1 ? $this->page : null;
        }

        foreach ($this->filters as $filter) {
            if ($filter->value != $filter->defaultValue) {
                $data[$filter->name] = $filter->value;
            }
        }

        return $data;
    }

    protected function prepareRendering()
    {

    }

    public function renderNavigationBarContent()
    {
        $c = $this->renderSearchInput();

        foreach ($this->filters as $filter) {
            $c .= '&nbsp;' . $filter->render();
        }

        $c .= $this->renderPagination() . $this->renderSetCount()
                . $this->renderNavigationBarRightContent($this->renderExportButton() . $this->renderAddButton());

        return $c;
    }

    protected function prepare()
    {
        $f = $this->task->request->get;
        $this->page = $f->asInt('page')->limit(1, 999999)->value;
        $this->search = $f->asString('search')->asInput(64)->value;
        $this->sortBy = $f->asString('sortBy')->asInput(64)->value;
        $this->sortDirection = $f->asList('sortDirection')->match(array('asc', 'desc'), 'desc')->value;

        foreach ($this->filters as $filter) {
            $filter->readValue($this->task->request->get);
        }
    }

    public function init()
    {
        $this->pageUrl = $this->task->module->getRouteUrl('list-sets', array('type' => $this->type)) . '?';

        if (!$this->entitiesPerPage) {
            $this->entitiesPerPage = $this->set->config->asFilter('ListPage')->get('setsPerPage', 20);
        }

        // Check whether search is available
        if ($this->set->getCapabilities()->search && $this->searchAvailable) {
            $searchable = false;
            foreach ($this->set->contents as $content) {
                if (!$content->enabled) {
                    continue;
                }

                if ($content->searchable) {
                    $searchable = true;
                    break;
                }
            }
            if ($this->searchAvailable && !$searchable) {
                $this->searchAvailable = false;
            }

        } else {
            $this->searchAvailable = false;
        }
    }

    protected function prepareLoadSetsOptions()
    {
        $options = array(
            'offset' => $this->entitiesPerPage ? ($this->page - 1) * $this->entitiesPerPage : 0,
            'count' => $this->entitiesPerPage,
            'search' => $this->search,
            'sort' => array('field' => $this->sortBy, 'direction' => $this->sortDirection)
        );

        foreach ($this->filters as $filter) {
            $options[$filter->name] = $filter->value;
        }

        return $options;
    }

    protected function loadSets()
    {
        $this->sets = $this->set->listSets($this->prepareLoadSetsOptions());
        $this->countPages = $this->entitiesPerPage ? ceil($this->sets->countAll / $this->entitiesPerPage) : 1;
    }

    public function getEditUrl($id, $type = null)
    {
        if (!$type) {
            $type = $this->type;
        }

        return $this->task->module->getRouteUrl('edit-set', array('type' => $type, 'id' => $id));
    }

    public function getAddUrl($type = null)
    {
        if (!$type) {
            $type = $this->type;
        }

        return $this->task->module->getRouteUrl('add-set', array('type' => $type));
    }

    public function getExportUrl($type = null, $parameters = null)
    {
        if (!$type) {
            $type = $this->type;
        }

        if ($parameters === null) {
            $parameters = $this->getUrlArguments('export');

        } else {
            $parameters = array_merge($this->getUrlArguments('export'), $parameters);
        }

        return $this->task->module->getRouteUrl('export-sets', array('type' => $type, 'format' => 'csv'), true, $parameters);
    }

    public function getDeleteUrl($id, $type = null)
    {
        if (!$type) {
            $type = $this->type;
        }

        return $this->task->module->getRouteUrl('delete-set', array('type' => $type, 'id' => $id));
    }

    protected function onRenderTableSetChanged()
    {

    }

    public function encodeArgs($args)
    {
        $s = '';

        foreach ($args as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if ($s !== '') {
                $s .= '&';
            }

            $s .= $key . '=' . rawurlencode($value);
        }

        return $s;
    }

    protected function onSubmit()
    {

    }

    protected function getExportFilename($format)
    {
        return $this->type . '_' . date('Y-m-d_H-i-s') . '.' . $format;
    }

    protected function generateExport($format)
    {
        if ($format != 'csv') {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $csv = new CsvWriter();
        $csv->open();

        $content = $this->set->firstContent;

        $fields = array();
        do {
            if (!$content->enabled || !$content->render) {
                continue;
            }

            $fields[] = $content->config->get('label');

        } while ($content = $content->nextContent);

        $csv->append($fields);

        while ($set = $this->sets->getNext()) {
            $fields = array();

            $this->onRenderTableSetChanged();

            $content = $this->set->firstContent;

            do {
                if (!$content->enabled || !$content->render) {
                    continue;
                }

                $fields[] = $content->render(Set::MODE_EXPORT);
            } while ($content = $content->nextContent);

            $csv->append($fields);
        }

        $data = chr(239) . chr(187) . chr(191) . $csv->getContents();
        $csv->close();

        $res = $this->response();
        $res->asDownload($this->getExportFilename($format));
        $res->postContent($data);
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
        $q = $this->factory()->quick();
        $u = $this->factory()->ui();

        $globalDelete = $this->set->allowDelete;
        $globalEdit = $this->set->allowEdit;

        $table = '<table><thead><tr>';

        $capabilities = $this->set->getCapabilities();

        $content = $this->set->firstContent;
        do {
            if (!$content->enabled || !$content->render) {
                continue;
            }

            $c = $content->config->get('label');
            if ($content->config->get('escapeLabel', true)) {
                $c = $q->e($c);
            }
            if ($capabilities->sort && $content->sortable) {
                $arguments = $this->getUrlArguments('sort');
                $arguments['sortBy'] = $content->name;

                if ($this->sortBy == $content->name && $this->sortDirection == 'desc') {
                    $arguments['sortDirection'] = 'asc';
                } else {
                    $arguments['sortDirection'] = 'desc';
                }

                $url = $this->pageUrl . $this->encodeArgs($arguments);

                $c = '<a href="' . $q->e($url) . '">' . $c . '</a>';
            }
            $table .= '<td>' . $c . '</td>';
        } while ($content = $content->nextContent);

        if ($globalEdit) {
            $table .= '<td>' . $q->t('Bearbeiten') . '</td>';
        }
        if ($globalDelete) {
            $table .= '<td>' . $q->t('Löschen') . '</td>';
        }

        $table .= '</tr></thead><tbody>';

        while ($set = $this->sets->getNext()) {
            $this->onRenderTableSetChanged();

            $content = $this->set->firstContent;

            $table .= '<tr>';
            do {
                if (!$content->enabled || !$content->render) {
                    continue;
                }

                $table .= '<td>' . $content->render('list');
            } while ($content = $content->nextContent);

            if ($globalEdit) {
                if ($this->set->allowEdit) {
                    $table .= '<td>' . $u->link($this->getEditUrl($this->set->getId()), $q->t('Bearbeiten')) . '</td>';
                } else {
                    $table .= '<td></td>';
                }
            }

            if ($globalDelete) {
                if ($this->set->allowDelete) {
                    $table .= '<td>' . $u->link($this->getDeleteUrl($this->set->getId()), $q->t('Löschen')) . '</td>';
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
        $u = $this->factory()->ui();

        return $u->formStart(
            $this->app->getUrl($this->task->request->url, true, $this->getUrlArguments('form')),
            'post'
        );
    }

    public function renderFormEnd()
    {
        $u = $this->factory()->ui();

        return $u->formEnd();
    }

    public function renderNavigationBar()
    {
        return '<div class="container">' . $this->renderNavigationBarContent() . '</div>';
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

        $q = $this->factory()->quick();

        return '<input type="text" placeholder="' . $q->e($q->t('Suche')) . '" name="search" class="updateOnChange" '
        . 'data-update-on-change-ignore-args="page" value="' . $q->e($this->search) . '" />';
    }

    public function renderPagination()
    {
        if (!$this->set->getCapabilities()->pagination) {
            return '';
        }

        $u = $this->factory()->ui();

        $pagerArgs = $this->getUrlArguments('pagination');
        $pagerArgs['page'] = '__page__';

        return $u->pageSelector(
            $u->calculatePageSelector($this->countPages, $this->page, 3, 3),
            $this->pageUrl . $this->encodeArgs($pagerArgs),
            array('pagePlaceholder' => '__page__')
        );
    }

    public function renderSetCount()
    {
        $q = $this->factory()->quick();

        $entries = $this->sets->countAll == 1 ? '1 Eintrag' : sprintf($q->t('%d Einträge'), $this->sets->countAll);

        return '<span class="countEntities">(' . $q->e($entries) . ')</span>';
    }

    public function renderAddButton()
    {
        $q = $this->factory()->quick();
        $u = $this->factory()->ui();

        if ($this->set->allowAdd) {
            $class = 'button';
            return $u->link($this->getAddUrl(), $q->t('Hinzufügen'), '_self', array('class' => $class));
        }

        return '';
    }

    public function renderExportButton()
    {
        $q = $this->factory()->quick();
        $u = $this->factory()->ui();

        if ($this->set->allowExport) {
            $class = 'button';
            return $u->link($this->getExportUrl(), $q->t('Exportieren'), '_self', array('class' => $class));
        }

        return '';
    }

    public function renderHeader()
    {
        $q = $this->factory()->quick();

        $c = '<h2>' . $q->e($this->set->config->get('title')) . '</h2>';
        if ($description = $this->set->config->get('description')) {
            $c .= '<p>' . $q->e($description) . '</p>';
        }
        $c .= '<hr />';

        return $c;
    }
}
