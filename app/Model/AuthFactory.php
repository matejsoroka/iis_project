<?php

namespace App\Model;

use Nette\Security\Permission;

class AuthFactory
{
    public static function create(): Permission
    {
        $acl = new Permission();

        // pokud chceme, můžeme role a zdroje načíst z databáze
        $acl->addRole('guest');
        $acl->addRole('student', 'guest');
        $acl->addRole('lector', 'student');
        $acl->addRole('garant', 'lector');
        $acl->addRole('leader', 'garant');
        $acl->addRole('admin', 'leader');

        $acl->addResource('Homepage:default');
//

//        $acl->deny('guest', 'backend');
        $acl->allow('student', 'Homepage:default');

        return $acl;
    }
}