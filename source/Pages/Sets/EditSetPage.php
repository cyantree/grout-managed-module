<?php
namespace Grout\Cyantree\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Set\Set;
use Cyantree\Grout\StatusContainer;
use Cyantree\Grout\Types\FileUpload;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Pages\ManagedPage;

class EditSetPage extends ManagedPage
{
    public $type;

    /** @var Set */
    public $set;

    /** @var StatusContainer */
    public $status;

    public $id;
    public $mode;

    public $submitUrl;
    public $deleteUrl;

    public function parseTask()
    {
        $type = $this->task->vars->get('type');
        if (!$this->factory()->module->setTypes->has($type)) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->type = $type;
        $this->id = $this->task->request->post->get('set_id', $this->task->vars->get('id'));
        $this->status = new StatusContainer();

        if (!$this->loadSet()) {
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->task->vars->set('menu', $type . '-sets');

        $q = ManagedFactory::get($this->app)->quick();

        if ($this->request()->post->get('save')) {
            $this->set->populate(
                $this->request()->post->data,
                FileUpload::fromMultiplePhpFileUploads($this->request()->files->data)
            );
            $this->set->check();

            if (!$this->set->status->error) {
                if (!$this->set->status->hasSuccessMessage('success')) {
                    $this->set->postSuccess('success', $q->t('Der Inhalt wurde erfolgreich gespeichert.'));
                }
                $this->set->save();
            }

        } else {
            $this->status = new StatusContainer();
        }

        if (!$this->set->getId()) {
            $this->submitUrl = $this->factory()->module->getRouteUrl('add-set', array('type' => $type));

        } else {
            $this->submitUrl = $this->factory()->module->getRouteUrl(
                'edit-set',
                array('type' => $type, 'id' => $this->set->getId())
            );
            $this->deleteUrl = $this->factory()->module->getRouteUrl(
                'delete-set',
                array('type' => $type, 'id' => $this->set->getId())
            );
        }

        // >> Translate status
        if ($this->status->hasSuccessMessages) {
            foreach ($this->status->successMessages as $message) {
                if ($message) {
                    $message->message = $q->t($message->message);
                }
            }
        }
        if ($this->status->hasErrorMessages) {
            foreach ($this->status->errors as $message) {
                if ($message) {
                    $message->message = $q->t($message->message);
                }
            }
        }
        if ($this->status->hasInfoMessages) {
            foreach ($this->status->infoMessages as $message) {
                if ($message) {
                    $message->message = $q->t($message->message);
                }
            }
        }

        $this->setResult($this->factory()->templates()->load('sets/edit.html'));
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

        $set->prepareRendering($this->mode);

        if ($this->id) {
            $set->loadById($this->id);

            if (!$set->getId() || !$set->allowEdit) {
                return false;
            }

        } else {
            $set->createNew();
        }

        $this->set = $set;

        return true;
    }
}
