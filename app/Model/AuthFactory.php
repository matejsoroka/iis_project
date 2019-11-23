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
        $acl->addResource('Course:default'); // Zobrazenie kurzov
        $acl->addResource('Course:detail'); // Zobrazenie kurzov
        $acl->addResource('Homepage:default'); // Zobrazenie hlavnej stránky
        $acl->addResource('Sign:in'); // Zobrazenie hlavnej stránky
        $acl->addResource('Sign:out'); // Zobrazenie hlavnej stránky
        $acl->addResource('Sign:up'); // Zobrazenie hlavnej stránky

        $acl->allow('guest', 'Course:detail');
        $acl->allow('guest', 'Course:default');
        $acl->allow('guest', 'Homepage:default');
        $acl->allow('guest', 'Sign:in');
        $acl->allow('guest', 'Sign:out');
        $acl->allow('guest', 'Sign:up');

        /* Student */
        $acl->addResource('Course:register'); // Registracia kurzov
        $acl->allow('student', 'Course:register');

        $acl->addResource('Index:default'); // Zobrazenie hodnotenia
        $acl->allow('student', 'Index:default');

        $acl->addResource('Course:files'); // Zobrazenie suborov
        $acl->allow('student', 'Course:files');

        $acl->addResource('Index:timetable'); // Zobrazenie hodnotenia
        $acl->allow('student', 'Index:timetable');

        /* Lektor */
        $acl->addResource('Course:edit'); // Uprava kurzu, pozor na restricted inputka
        $acl->allow('leader', 'Course:edit');

        /* Garant */
        $acl->addResource("ShowCourseStatus");
        $acl->allow("garant", "ShowCourseStatus");

        /* Leader */
        $acl->addResource("Room:default");
        $acl->allow("leader", "Room:default");

        $acl->addResource('Room:edit'); // Sprava miestnosti
        $acl->allow('leader', 'Room:edit');

        $acl->addResource("EditCourseStatus");
        $acl->allow("leader", "EditCourseStatus");

        /* Admin */
        $acl->addResource('User:default'); // Zoznam uzivatelov
        $acl->allow('admin', 'User:default');

        $acl->addResource('User:edit'); // Uprava uzivatela uzivatelov
        $acl->allow('admin', 'User:edit');

        return $acl;
    }
}