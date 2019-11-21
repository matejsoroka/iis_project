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

//        $acl->addResource('backend');
//
//        $acl->allow('admin', 'backend');
//        $acl->deny('guest', 'backend');
//
//        // případ A: role admin má menší váhu než role guest
//        $acl->addRole('john', ['admin', 'guest']);
//        $acl->isAllowed('john', 'backend'); // false
//
//        // případ B: role admin má větší váhu než guest
//        $acl->addRole('mary', ['guest', 'admin']);
//        $acl->isAllowed('mary', 'backend'); // true

        return $acl;
    }
}