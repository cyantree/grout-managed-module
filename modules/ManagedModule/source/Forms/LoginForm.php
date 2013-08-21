<?php
namespace Grout\ManagedModule\Forms;

use Cyantree\Grout\App\Task;
use Cyantree\Grout\Form\Form;
use Grout\ManagedModule\ManagedFactory;

class LoginForm extends Form
{
    /** @var LoginFormData */
    public $data;

    /** @var Task */
    public $task;

    private $_loginEvent;

    protected function _createDataObject()
    {
        return new LoginFormData();
    }

    protected function _getData()
    {
        $this->data->username = $this->dataIn->get('username');
        $this->data->password = $this->dataIn->get('password');
    }

    protected function _checkData()
    {
        $a = ManagedFactory::get($this->task->app);
        $q = $a->appQuick();

        $this->_loginEvent = $this->task->module->events->trigger('login',
            array('task' => $this->task, 'username' => $this->data->username, 'password' => $this->data->password,
            'success' => null, 'userId' => null, 'userRole' => null));

        $data = $this->_loginEvent->data;
        $success = $data['success'];
        if($success === false){
            $this->status->postError('invalidCredentials', $q->t('Bitte prüfen Sie die Anmeldedaten.'));
        }elseif($success === null){
            $this->_loginEvent->data['userId'] = $a->appConfig()->username;
            $this->_loginEvent->data['userRole'] = 'admin';
            if($this->data->username !== $a->appConfig()->username || $this->data->password !== $a->appConfig()->password){
                $this->status->postError('invalidCredentials', $q->t('Bitte prüfen Sie die Anmeldedaten.'));
            }
        }
    }

    protected function _submit()
    {
        $q = ManagedFactory::get($this->task->app)->appQuick();

        $this->_finishForm();

        $data = ManagedFactory::get($this->task->app)->appSessionData();
        $data->set('userId', $this->_loginEvent->data['userId']);
        $data->set('userRole', $this->_loginEvent->data['userRole']);

        $this->status->postSuccess(null, $q->t('Sie wurden erfolgreich angemeldet.'));
    }
}

class LoginFormData
{
    public $username;
    public $password;
}