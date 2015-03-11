<?php
namespace Grout\Cyantree\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Set\Set;
use Grout\Cyantree\ManagedModule\Pages\ManagedPage;

class DeleteSetPage extends ManagedPage
{
    public $type;
    /** @var Set */
    public $set;

    public $id;

    public $deleted = false;

    public function parseTask()
    {
        $type = $this->task->vars->get('type');
        if (!$this->factory()->module->setTypes->has($type)) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->type = $type;
        $this->id = $this->task->request->post->get('id', $this->task->vars->get('id'));

        if (!$this->loadSet()) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->set->status->setTranslator($this->factory()->translator());

        $this->task->vars->set('menu', $type . '-sets');

        if ($this->request()->post->get('delete')) {
            if ($this->set->delete()) {
                $this->deleted = true;

                if (!$this->set->status->success->has('success')) {
                    $this->set->postSuccess('success', _('Der Inhalt wurde erfolgreich gelöscht.'));
                }
            } else {
                if (!$this->set->status->error->has('error')) {
                    $this->set->postError('error', _('Der Inhalt konnte nicht gelöscht werden.'));
                }
            }
        }

        $this->renderPage();
    }

    public function renderPage()
    {
        $this->setTemplateResult('sets/delete.html');
    }

    public function getSubmitUrl()
    {
        return $this->factory()->module->getRouteUrl(
                'delete-set',
                array('type' => $this->type, 'id' => $this->set->getId())
        );
    }

    private function loadSet()
    {
        $class = $this->factory()->module->setTypes->get($this->type);

        if (!$class) {
            return false;
        }

        /** @var $set Set */
        $this->set = $set = new $class($this->task);

        $acl = $this->factory()->acl()->factory()->acl();
        $setConfig = $this->factory()->setTools()->getConfig($this->type);
        $set->allowAdd = $setConfig->addPageAccess ? $acl->satisfies($setConfig->addPageAccess) : true;
        $set->allowEdit = $setConfig->editPageAccess ? $acl->satisfies($setConfig->editPageAccess) : true;
        $set->allowDelete = $setConfig->deletePageAccess ? $acl->satisfies($setConfig->deletePageAccess) : true;
        $set->allowExport = $setConfig->exportPageAccess ? $acl->satisfies($setConfig->exportPageAccess) : true;
        $set->allowList = $setConfig->listPageAccess ? $acl->satisfies($setConfig->listPageAccess) : true;

        $set->init(Set::MODE_DELETE, Set::FORMAT_HTML, $this->factory()->config()->setContext);

        $this->init();
        $this->prepare();

        if (!$set->allowDelete) {
            return false;
        }

        $set->loadById($this->id);

        if (!$set->getId() || !$set->allowDelete) {
            return false;
        }

        return true;
    }

    public function init()
    {

    }

    public function prepare()
    {

    }
}
