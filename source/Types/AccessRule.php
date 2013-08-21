<?php
namespace Grout\ManagedModule\Types;

class AccessRule
{
    public $userIds;
    public $userRoles;
    
    public $callbacks;
    public $access;

    public function __construct($userIds = null, $userRoles = null, $callbacks = null, $access = null){
        $this->userIds = $userIds;
        $this->userRoles = $userRoles;
        $this->callbacks = $callbacks;
        $this->access = $access;
    }
    
    public function permitUserId($id)
    {
        if($this->userIds === null){
            $this->userIds = array();
        }
        
        $this->userIds[$id] = true;
    }
    
    public function permitUserRole($role)
    {
        if($this->userRoles === null){
            $this->userRoles = array();
        }

        $this->userRoles[$role] = true;
    }

    public function forbidUserId($id)
    {
        if($this->userIds === null){
            $this->userIds = array();
        }

        $this->userIds[$id] = false;
    }

    public function forbidUserRole($role)
    {
        if($this->userRoles === null){
            $this->userRoles = array();
        }

        $this->userRoles[$role] = false;
    }

    public function addCallback($callback)
    {
        if($this->callbacks === null){
            $this->callbacks = array();
        }

        $this->callbacks[] = $callback;
    }
    
    public function hasAccess($userId, $userRole)
    {
        if($this->access !== null){
            return $this->access;
        }

        if($this->userIds !== null && array_key_exists($userId, $this->userIds)){
            return $this->userIds[$userId];
        }elseif($this->userRoles !== null && array_key_exists($userRole, $this->userRoles)){
            return $this->userRoles[$userRole];
        }elseif($this->callbacks !== null){
            foreach($this->callbacks as $callback){
                $res = call_user_func($callback, $userId, $userRole);

                if($res === true || $res === false){
                    return $res;
                }
            }

            return false;
        }else{
            return false;
        }
    }
}