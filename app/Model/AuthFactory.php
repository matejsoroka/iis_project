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

        /* Guest */
        $acl->addResource('Curse:default'); // Zobrazenie kurzov
        $acl->addResource('Curse:detail'); // Zobrazenie kurzov
        $acl->addResource('Homepage:default'); // Zobrazenie hlavnej stránky
        $acl->addResource('Sign:in'); // Zobrazenie hlavnej stránky
        $acl->addResource('Sign:out'); // Zobrazenie hlavnej stránky
        $acl->addResource('Sign:up'); // Zobrazenie hlavnej stránky

        $acl->allow('guest', 'Curse:detail');
        $acl->allow('guest', 'Curse:default');
        $acl->allow('guest', 'Homepage:default');
        $acl->allow('guest', 'Sign:in');
        $acl->allow('guest', 'Sign:out');
        $acl->allow('guest', 'Sign:up');

        /* Student */
        $acl->addResource('Curse:register'); // Registracia kurzov
        $acl->allow('student', 'Curse:register');

        $acl->addResource('Index:default'); // Zobrazenie hodnotenia
        $acl->allow('student', 'Index:default');

        $acl->addResource('Curse:files'); // Zobrazenie suborov
        $acl->allow('student', 'Curse:files');

        $acl->addResource('Index:timetable'); // Zobrazenie hodnotenia
        $acl->allow('student', 'Index:timetable');

        /* Lektor */
        $acl->addResource('Curse:edit'); // Uprava kurzu, pozor na restricted inputka
        $acl->allow('leader', 'Curse:edit');

        /* Leader */
        $acl->addResource('Room:edit'); // Sprava miestnosti
        $acl->allow('leader', 'Room:edit');

        /* Admin */
        $acl->addResource('User:default'); // Zoznam uzivatelov
        $acl->allow('admin', 'User:default');

        $acl->addResource('User:edit'); // Uprava uzivatela uzivatelov
        $acl->allow('admin', 'User:edit');

        return $acl;
    }
}