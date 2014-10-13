<?php
namespace Grout\Cyantree\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Set\Set;
use Cyantree\Grout\StatusContainer;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Pages\ManagedPage;
use Grout\Cyantree\ManagedModule\Pages\RestrictedPage;

class DeleteSetPage extends ManagedPage
{
    public $type;
    /** @var Set */
    public $set;

    /** @var StatusContainer */
    public $status;

    public $id;

    public $submitUrl;

    public $deleted = false;

    public function parseTask()
    {
        $type = $this->task->vars->get('type');
        if (!$this->factory()->module->setTypes->has($type)) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->status = new StatusContainer();
        $this->type = $type;
        $this->id = $this->task->request->post->get('id', $this->task->vars->get('id'));

        if (!$this->loadSet()) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $q = ManagedFactory::get($this->app)->quick();

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

        $this->setResult($this->factory()->templates()->load('sets/delete.html'));
    }

    private function loadSet()
    {
        $class = $this->factory()->module->setTypes->get($this->type);

        if (!$class) {
            return false;
        }

        /** @var $set Set */
        $set = new $class($this->task);

        $acl = $this->factory()->acl()->factory()->acl();
        $setConfig = $this->factory()->setTools()->getConfig($this->type);
        $set->allowAdd = $setConfig->addPageAccess ? $acl->satisfies($setConfig->addPageAccess) : true;
        $set->allowEdit = $setConfig->editPageAccess ? $acl->satisfies($setConfig->editPageAccess) : true;
        $set->allowDelete = $setConfig->deletePageAccess ? $acl->satisfies($setConfig->deletePageAccess) : true;
        $set->allowExport = $setConfig->exportAccess ? $acl->satisfies($setConfig->exportAccess) : true;
        $set->allowList = $setConfig->listPageAccess ? $acl->satisfies($setConfig->listPageAccess) : true;

        $set->init();

        $set->prepareRendering(Set::MODE_DELETE);

        if (!$set->allowDelete) {
            return false;
        }

        $set->loadById($this->id);
        $this->set = $set;

        if (!$set->getId() || !$set->allowDelete) {
            return false;
        }

        return true;
    }
}
