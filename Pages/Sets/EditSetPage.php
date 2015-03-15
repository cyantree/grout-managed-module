<?php
namespace Grout\Cyantree\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Set\Set;
use Cyantree\Grout\Types\FileUpload;
use Grout\Cyantree\ManagedModule\Pages\ManagedPage;

class EditSetPage extends ManagedPage
{
    public $type;

    /** @var Set */
    public $set;

    public $id;
    public $mode;

    public function parseTask()
    {
        $type = $this->task->vars->get('type');
        if (!$this->factory()->module->setTypes->has($type)) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->type = $type;
        $this->id = $this->task->request->post->get('set_id', $this->task->vars->get('id'));

        if (!$this->loadSet()) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->set->status->setTranslator($this->factory()->translator());

        $this->task->vars->set('menu', $type . '-sets');

        $resetOnSuccess = false;
        $doSave = false;
        $isNew = !$this->set->getId();

        if ($this->request()->post->get('saveAndNew')) {
            $resetOnSuccess = $doSave = true;

        } elseif ($this->request()->post->get('save')) {
            $doSave = true;
        }

        if ($doSave) {
            $this->set->populate(
                $this->request()->post->getData(),
                FileUpload::fromMultiplePhpFileUploads($this->request()->files->getData())
            );
            $this->set->check();

            if (!$this->set->status->error->hasStatuses) {
                if (!$this->set->status->success->has('success')) {
                    $this->set->postSuccess('success', _('Der Inhalt wurde erfolgreich gespeichert.'));
                }
                $this->set->save();
            }

        }

        if ($isNew && $resetOnSuccess && $this->set->status->success->hasStatuses) {
            $this->set->createNew();
        }

        $this->renderPage();
    }

    public function renderPage()
    {
        $this->setTemplateResult('sets/edit.html');
    }

    public function getSubmitUrl()
    {
        $id = $this->set->getId();

        if ($id) {
            return $this->factory()->module->getRouteUrl(
                    'edit-set',
                    array('type' => $this->type, 'id' => $this->set->getId())
            );

        } else {
            return $this->factory()->module->getRouteUrl('add-set', array('type' => $this->type));
        }
    }

    public function getDeleteUrl()
    {
        $id = $this->set->getId();

        if ($id) {
            return $this->factory()->module->getRouteUrl(
                    'delete-set',
                    array('type' => $this->type, 'id' => $id)
            );

        } else {
            return null;
        }
    }

    protected function loadSet()
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

        if ($this->id) {
            $this->mode = Set::MODE_EDIT;

            if (!$set->allowEdit) {
                return false;
            }

        } else {
            $this->mode = Set::MODE_ADD;

            if (!$set->allowAdd) {
                return false;
            }
        }

        $set->init($this->mode, Set::FORMAT_HTML, $this->factory()->config()->setContext);

        $this->init();
        $this->prepare();

        if ($this->id) {
            $set->loadById($this->id);

            if (!$set->getId() || !$set->allowEdit) {
                return false;
            }

        } else {
            $set->createNew();
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
