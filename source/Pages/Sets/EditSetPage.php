<?php
namespace Grout\Cyantree\ManagedModule\Pages\Sets;

use Cyantree\Grout\App\Types\ResponseCode;
use Cyantree\Grout\Form\FormStatus;
use Cyantree\Grout\Set\Set;
use Grout\Cyantree\ManagedModule\ManagedFactory;
use Grout\Cyantree\ManagedModule\Pages\RestrictedPage;

class EditSetPage extends RestrictedPage
{
    public $type;

    /** @var Set */
    public $set;

    /** @var FormStatus */
    public $status;

    public $id;
    public $mode;

    public $submitUrl;
    public $deleteUrl;

    protected function _onAccessible()
    {
        $type = $this->task->vars->get('type');
        if(!$this->factory()->module->setTypes->has($type)){
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $this->type = $type;
        $this->id = $this->task->request->post->get('set_id', $this->task->vars->get('id'));

        if(!$this->_loadSet()){
            $this->parseError(ResponseCode::CODE_404);
            return;
        }

        $q = ManagedFactory::get($this->app)->appQuick();

        $this->set->prepareRendering($this->mode);

        if ($this->request()->post->get('save')) {
            $this->status = $this->set->status;

            $this->set->populate($this->request()->post->data);
            $this->set->check();

            if(!$this->set->status->error){
                if (!$this->set->status->hasSuccessMessage('success')) {
                    $this->set->postSuccess('success', $q->t('Der Inhalt wurde erfolgreich gespeichert.'));
                }
                $this->set->save();
            }
        }else{
            $this->status = new FormStatus();
        }

        if (!$this->set->getId()) {
            $this->submitUrl = $this->factory()->module->getRouteUrl('add-set', array('type' => $type));
        } else {
            $this->submitUrl = $this->factory()->module->getRouteUrl('edit-set', array('type' => $type, 'id' => $this->set->getId()));
            $this->deleteUrl = $this->factory()->module->getRouteUrl('delete-set', array('type' => $type, 'id' => $this->set->getId()));
        }

        // >> Translate status
        if($this->status->hasSuccessMessages){
            foreach($this->status->successMessages as $code => $message){
                if($message){
                    $message->message = $q->t($message->message);
                }
            }
        }
        if($this->status->hasErrorMessages){
            foreach($this->status->errors as $code => $message){
                if($message){
                    $message->message = $q->t($message->message);
                }
            }
        }
        if($this->status->hasInfoMessages){
            foreach($this->status->infoMessages as $message){
                if($message){
                    $message->message = $q->t($message->message);
                }
            }
        }

        $this->setResult($this->factory()->appTemplates()->load('sets/edit.html'));
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
        if($this->id){
            $set->loadById($this->id);

            if(!$set->getId() || !$set->allowEdit){
                return false;
            }
        }elseif(!$set->allowAdd){
            return false;
        }

        if($set->getId()){
            $this->mode = Set::MODE_EDIT;
        }else{
            $set->createNew();
            $this->mode = Set::MODE_ADD;
        }

        $this->set = $set;

        return true;
    }
}