<?php
namespace Grout\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Filter\ArrayFilter;
use Cyantree\Grout\Filter\ListFilter;
use Cyantree\Grout\Filter\NumberFilter;
use Cyantree\Grout\Filter\StringFilter;
use Cyantree\Grout\Set\DoctrineSet;
use Cyantree\Grout\Set\Set;
use Cyantree\Grout\Tools\StringTools;
use Grout\ManagedModule\ManagedFactory;
use Grout\ManagedModule\Forms\LoginForm;
use Grout\ManagedModule\Pages\RestrictedPage;
use Grout\ManagedModule\TestFactory;

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

    public $searchArg;
    public $orderArg;
    public $pageArg;

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
        $this->initConfigurationWithArray($this->task->request->get->getData());

        $this->setResult($this->managedFactory()->appTemplates()->load('sets/list.html'));
    }

    public function init()
    {
        // Filter configurations
        $this->page = NumberFilter::filter($this->page)->limit(1, 999999)->value;
        $this->entitiesPerPage = NumberFilter::filter($this->entitiesPerPage, 0)->limit(0, 500)->value;
        $this->search = StringFilter::filter($this->search)->asInput(64)->value;
        $this->orderByDirection = ListFilter::filter($this->orderByDirection)->match(array('asc', 'desc'), 'desc')->value;

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

        // Init args
        if($this->page > 1){
            $this->pageArg = '&page='.$this->page;
        }
        if($this->search != ''){
            $this->searchArg = '&search='.$this->search;
        }
        if($this->orderBy != ''){
            $this->orderArg = '&orderBy='.$this->orderBy.'&orderDirection='.$this->orderByDirection;
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

    public function loadEntities()
    {
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
            $whereClause = ' WHERE ('.implode(' OR ', $searchQueries).')';
        }else{
            $whereClause = '';
        }
        $orderClause = '';

        // Check for ordering
        foreach($this->set->contents as $content){
            if($content->sortable && $content->name == $this->orderBy){
                $orderClause = ' ORDER BY e.'.$content->name.' '.$this->orderByDirection;
                break;
            }
        }

        // Get items
        $query = $this->globalFactory()->appDoctrine()->createQuery('SELECT e FROM '.$this->entityClass.' e'.$whereClause.$orderClause);
        $query->setFirstResult(($this->page - 1) * $this->entitiesPerPage);
        $query->setMaxResults($this->entitiesPerPage);

        if($parameters){
            $query->setParameters($parameters);
        }

        $this->entities = $query->getResult();

        $query = $this->globalFactory()->appDoctrine()->createQuery('SELECT COUNT(e) FROM '.$this->entityClass.' e'.$whereClause);
        if($parameters){
            $query->setParameters($parameters);
        }

        $countEntities = $query->getSingleScalarResult();

        $this->countPages = ceil($countEntities / $this->entitiesPerPage);

        $this->set->onList($this->entities);
    }

    public function initConfigurationWithArray($getData)
    {
        $f = new ArrayFilter($getData);
        $this->page = $f->get('page');
        $this->search = $f->get('search');
        $this->orderBy = $f->get('orderBy');
        $this->orderByDirection = $f->get('orderDirection');
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
                    $url = $this->pageUrl.$this->searchArg.'&orderBy='.$content->name;

                    if($this->orderBy == $content->name && $this->orderByDirection == 'desc'){
                        $url .= '&orderDirection=asc';
                    }else{
                        $url .= '&orderDirection=desc';
                    }
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

    public function renderNavBar()
    {
        $q = ManagedFactory::get($this->app)->appQuick();
        $u = $this->managedFactory()->appUi();

        $c = '<div class="container">';
        $c .= '<input type="text" placeholder="'.$q->e($q->t('Suche')).'" name="search" value="'.$q->e($this->search).'" />';
        $c .= $u->pageSelector($u->calculatePageSelector($this->countPages, $this->page, 3, 3), $this->pageUrl.$this->searchArg.$this->orderArg.'&page=%page%');

        if($this->set->allowAdd){
            $c .= $u->link($this->getAddUrl(), $q->t('Hinzufügen'), '_self', array('class' => 'button absoluteRight'));
        }
        $c .= '</div>';

        return $c;
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
        $escapedUrl = StringTools::escapeJs($this->pageUrl);
        $c = <<<CNT
        <script>
          $('input[name="search"]').keydown(function(e){
                  if(e.which == 13){
                      window.location.href = '{$escapedUrl}&search=' + encodeURIComponent($(this).val());
        }
              });
        </script>
CNT;

        return $c;
    }
}