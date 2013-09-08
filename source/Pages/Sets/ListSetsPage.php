<?php
namespace Grout\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Filter\ListFilter;
use Cyantree\Grout\Filter\NumberFilter;
use Cyantree\Grout\Filter\StringFilter;
use Cyantree\Grout\Set\DoctrineSet;
use Cyantree\Grout\Set\Set;
use Cyantree\Grout\Tools\StringTools;
use Grout\ManagedModule\ManagedFactory;
use Grout\ManagedModule\Pages\RestrictedPage;

class ListSetsPage extends RestrictedPage
{
    public $type;

    public $search;
    public $page;
    public $entitiesPerPage;
    public $countPages;

    public $orderBy;
    public $orderByDirection;

    public $pageUrl;

    /** @var DoctrineSet */
    public $set;
    public $entityClass;

    public $entities;

    public $searchAvailable = true;

    protected function _onAccessible()
    {
        $type = $this->task->vars->get('type');

        if(!$this->managedFactory()->appModule()->setTypes->has($type)){
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $setClass = $this->managedFactory()->appModule()->setTypes->get($type);

        // Is no valid set type
        if(!$setClass){
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        // Retrieve current set class
        $setClass = $setClass::${'_CLASS_'};
        $this->set = new $setClass($this->task);

        if(!$this->set->allowList){
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->type = $type;

        $this->task->vars->set('menu', $type.'-sets');

        $this->init();
        $this->_prepare();
        $this->_loadEntities();
        $data = $this->_render();

        if ($data['content']) {
            $this->setResult($data['content']);
        } else {
            $this->setResult($this->managedFactory()->appTemplates()->load($data['template'], $data));
        }
    }

    public function getUrlArguments($context)
    {
        return array(
            'orderBy' => $this->orderBy,
            'orderDirection' => $this->orderByDirection == 'desc' ? null : 'desc',
            'search' => $this->search,
            'page' => $this->page > 1 ? $this->page : null
        );
    }

    protected function getListQueryData()
    {
        return $this->set->getListQueryData();
    }

    protected function _render()
    {
        return array(
            'scripts' => '',
            'readyScripts' => '',
            'belowHeader' => '',
            'aboveList' => '',
            'belowList' => '',
            'footer' => '',
            'navBar' => $this->renderSearchInput().$this->renderPagination().$this->renderAddButton(),
            'template' => 'sets/list.html',
            'content' => ''
        );
    }

    protected function _prepare()
    {
        $f = $this->task->request->get;
        $this->page = $f->get('page');
        $this->search = $f->get('search');
        $this->orderBy = $f->get('orderBy');
        $this->orderByDirection = $f->get('orderDirection');
    }

    public function init()
    {
        $entityClass = $this->managedFactory()->appModule()->setTypeEntities->get($this->type);

        // Is no valid entity
        if(!$entityClass){
            return false;
        }

        $this->entityClass = $entityClass::${'_CLASS_'};

        $this->pageUrl = $this->task->module->getRouteUrl('list-sets', array('type' => $this->type)).'?';

        // Prepare rendering
        $this->set->prepareRendering(Set::MODE_LIST);

        if(!$this->entitiesPerPage){
            $this->entitiesPerPage = $this->set->config->get('count', 10);
        }

        // Check whether search is available
        if($this->searchAvailable){
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
        }

        return true;
    }

    protected function _loadEntities()
    {
        // Filter configurations
        $this->page = NumberFilter::filter($this->page)->limit(1, 999999)->value;
        $this->entitiesPerPage = NumberFilter::filter($this->entitiesPerPage, 0)->limit(0, 500)->value;
        $this->search = StringFilter::filter($this->search)->asInput(64)->value;
        $this->orderByDirection = ListFilter::filter($this->orderByDirection)->match(array('asc', 'desc'), 'desc')->value;

        $parameters = array();

        // Create search queries
        $searchQueries = array();
        if($this->searchAvailable && $this->search != ''){
            foreach($this->set->contents as $content){
                if($content->searchable){
                    $parameters['search'] = '%'.$this->search.'%';
                    $searchQueries[] = 'e.'.$content->name.' LIKE :search';
                }
            }
        }


        // Create query parts
        if($searchQueries){
            $filterClause = '('.implode(' OR ', $searchQueries).')';
        }else{
            $filterClause = '1 = 1';
        }

        // Create queries
        $data = $this->getListQueryData();

        $orderClause = '';

        // Check for ordering
        foreach($this->set->contents as $content){
            if($content->sortable && $content->name == $this->orderBy){
                $orderClause = 'e.'.$content->name.' '.$this->orderByDirection;
                break;
            }
        }

        if($orderClause === '') {
            if ($data['select']['defaultOrder']) {
                $orderClause = $data['select']['defaultOrder'];
            } else {
                $orderField = $this->set->config->get('order');
                if ($orderField) {
                    $orderClause = 'e.'.$orderField;
                } else {
                    $identifiers = $this->globalFactory()->appDoctrine()->getClassMetadata($this->entityClass)->getIdentifierFieldNames();
                    $orderClause = 'e.'.$identifiers[0].' DESC';
                }
            }
        }

        $queryLookUps = array(
            '{e}',
            '{entity}',
            '{filter}',
            '{order}',
        );
        $queryReplaces = array(
            'e',
            $this->entityClass.' e',
            $filterClause,
            $orderClause
        );

        // Get items
        $queryData = $data['select'];
        $query = str_replace($queryLookUps, $queryReplaces, $queryData['query']);
        $query = $this->globalFactory()->appDoctrine()->createQuery($query);
        $query->setFirstResult(($this->page - 1) * $this->entitiesPerPage);
        $query->setMaxResults($this->entitiesPerPage);

        if ($parameters || $queryData['parameters']) {
            $query->setParameters(array_merge($parameters, $queryData['parameters']));
        }

        $this->entities = $query->getResult();

        // Get count
        $queryData = $data['count'];
        $query = str_replace($queryLookUps, $queryReplaces, $queryData['query']);

        $query = $this->globalFactory()->appDoctrine()->createQuery($query);
        if ($parameters || $queryData['parameters']) {
            $query->setParameters(array_merge($parameters, $queryData['parameters']));
        }

        $countEntities = $query->getSingleScalarResult();

        $this->countPages = ceil($countEntities / $this->entitiesPerPage);

        $this->set->onList($this->entities);
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

    public function getDeleteUrl($id, $type = null)
    {
        if(!$type){
            $type = $this->type;
        }

        return $this->task->module->getRouteUrl('delete-set', array('type' => $type, 'id' => $id));
    }

    public function renderTable()
    {
        $q = ManagedFactory::get($this->app)->appQuick();
        $u = $this->managedFactory()->appUi();

        $table = '<table><thead><tr>';

        $content = $this->set->firstContent;
        do{
            if($content->config->get('visible')){
                $c = $q->e($content->config->get('label'));
                if($content->sortable){
                    $arguments = $this->getUrlArguments('sort');
                    $arguments['orderBy'] = $content->name;

                    if($this->orderBy == $content->name && $this->orderByDirection == 'desc'){
                        $arguments['orderDirection'] = 'asc';
                    }else{
                        $arguments['orderDirection'] = 'desc';
                    }

                    $url = $this->pageUrl.$this->encodeArgs($arguments);

                    $c = '<a href="'.$q->e($url).'">'.$c.'</a>';
                }
                $table .= '<td>'.$c.'</td>';
            }
        }while($content = $content->nextContent);

        if($this->set->allowEdit){
            $table .= '<td>'.$q->t('Bearbeiten').'</td>';
        }
        if($this->set->allowDelete){
            $table .= '<td>'.$q->t('Löschen').'</td>';
        }

        $table .= '</tr></thead><tbody>';

        foreach($this->entities as $entity){
            $this->set->setEntity($entity);

            $content = $this->set->firstContent;

            $table .= '<tr>';
            do{
                if($content->config->get('visible')){
                    $table .= '<td>'.$content->render('list');
                }
            }while($content = $content->nextContent);

            if($this->set->allowEdit){
                $table .= '<td>'.$u->link($this->getEditUrl($this->set->getId()), $q->t('Bearbeiten')).'</td>';
            }

            if($this->set->allowDelete){
                $table .= '<td>'.$u->link($this->getDeleteUrl($this->set->getId()), $q->t('Löschen')).'</td>';
            }

            $table .= '</tr>';
        }

        $table .= '</tbody></table>';

        return $table;
    }

    public function encodeArgs($args)
    {
        $s = '';

        foreach ($args as $key => $value) {
            if($value === null || $value === '') {
                continue;
            }

            $s .= '&'.$key.'='.rawurlencode($value);
        }

        return $s;
    }

    public function renderNavBar($content)
    {
        return '<div class="container">'.$content.'</div>';
    }

    public function renderSearchInput()
    {
        $q = ManagedFactory::get($this->app)->appQuick();

        return '<input type="text" placeholder="'.$q->e($q->t('Suche')).'" name="search" class="updateOnChange" value="'.$q->e($this->search).'" />';
    }

    public function renderPagination()
    {
        $u = $this->managedFactory()->appUi();

        $pagerArgs = $this->getUrlArguments('pagination');
        $pagerArgs['page'] = null;

        return $u->pageSelector($u->calculatePageSelector($this->countPages, $this->page, 3, 3), $this->pageUrl.$this->encodeArgs($pagerArgs).'&page=%page%');
    }

    public function renderAddButton($alignRight = true)
    {
        $q = ManagedFactory::get($this->app)->appQuick();
        $u = $this->managedFactory()->appUi();

        if($this->set->allowAdd){
            $class = 'button';
            if ($alignRight) {
                $class .= ' absoluteRight';
            }
            return $u->link($this->getAddUrl(), $q->t('Hinzufügen'), '_self', array('class' => $class));
        }

        return '';
    }

    public function renderHeader()
    {
        $q = $this->globalFactory()->appQuick();

        $c = '<h2>'.$q->e($this->set->config->get('title')).'</h2>';
        if($description = $this->set->config->get('description')){
            $c .= '<p>'.$q->e($description).'</p>';
        }
        $c .= '<hr />';

        return $c;
    }

    public function renderFooter()
    {
        return '';
    }
}