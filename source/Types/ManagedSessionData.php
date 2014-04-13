<?php
namespace Grout\Cyantree\ManagedModule\Types;

class ManagedSessionData
{
    public $userId;
    public $userRole;

    public function login($userId, $userRole = 'admin')
    {
        $this->userId = $userId;
        $this->userRole = $userRole;
    }

    public function isLoggedIn()
    {
        return $this->userId !== null;
    }

    public function reset()
    {
        $this->userId = $this->userRole = null;
    }
}