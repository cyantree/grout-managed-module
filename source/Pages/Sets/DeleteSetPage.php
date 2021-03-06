<?php
namespace Grout\Cyantree\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Set\Set;
use Cyantree\Grout\StatusContainer;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Pages\RestrictedPage;

class DeleteSetPage extends RestrictedPage
{
    public $type;
    /** @var Set */
    public $set;

    /** @var StatusContainer */
    public $status;

    public $id;

    public $submitUrl;

    public $deleted = false;

    protected function _onAccessible()
    {
        $type = $this->task->vars->get('type');
        if(!$this->factory()->module->setTypes->has($type)){
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->status = new StatusContainer();
        $this->type = $type;
        $this->id = $this->task->request->post->get('id', $this->task->vars->get('id'));

        if(!$this->_loadSet()){
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $q = ManagedFactory::get($this->app)->appQuick();

        if ($this->request()->post->get('delete')) {
            if ($this->set->delete()) {
                $this->deleted = true;

                if (!$this->set->status->hasSuccessMessage('success')) {
                    $this->set->postSuccess('success', $q->t('Der Inhalt wurde erfolgreich gelöscht.'));
                }
            } else {
                if (!$this->set->status->hasError('error')) {
                    $this->set->postError('error', $q->t('Der Inhalt konnte nicht gelöscht werden.'));
                }
            }
        }

        $this->submitUrl = $this->factory()->module->getRouteUrl('delete-set', array('type' => $type, 'id' => $this->set->getId()));

        $this->setResult($this->factory()->appTemplates()->load('sets/delete.html'));
    }

    private function _loadSet()
    {
        $class = $this->factory()->module->setTypes->get($this->type);

        if(!$class){
            return false;
        }

        /** @var $set Set */
        $class = $class::${'_CLASS_'};
        $set = new $class($this->task);

        $set->prepareRendering(Set::MODE_DELETE);

        if (!$set->allowDelete) {
            return false;
        }

        $set->loadById($this->id);
        $this->set = $set;

        if(!$set->getId() || !$set->allowDelete){
            return false;
        }

        return true;
    }
}