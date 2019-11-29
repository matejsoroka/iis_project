<?php

namespace App\Model;

use Nette\Security\Permission;

class AuthFactory
{
    public static function create(): Permission
    {
        $acl = new Permission();

        $acl->addRole('guest');
        $acl->addRole('registered', 'guest');
        $acl->addRole('student', 'guest');
        $acl->addRole('lector', 'student');
        $acl->addRole('garant', 'lector');
        $acl->addRole('leader', 'garant');
        $acl->addRole('admin', 'leader');

        /* Guest */
        $acl->addResource('Homepage:default');      // Zobrazenie hlavnej stránky
        $acl->addResource('Course:default');        // Zobrazenie kurzov
        $acl->addResource('Course:detail');         // Zobrazenie kurzov
        $acl->addResource('Sign:in');               // Prihlasenie
        $acl->addResource('Sign:out');              // Odhlasenie
        $acl->addResource('Sign:up');               // Registracia

        $acl->allow('guest', 'Homepage:default');
        $acl->allow('guest', 'Course:detail');
        $acl->allow('guest', 'Course:default');
        $acl->allow('guest', 'Sign:in');
        $acl->allow('guest', 'Sign:out');
        $acl->allow('guest', 'Sign:up');

        /* Student */
        $acl->addResource('Course:register');       // Registracia kurzov
        $acl->allow('student', 'Course:register');

        $acl->addResource('Timetable:default');     // Registracia kurzov
        $acl->allow('student', 'Timetable:default');

        $acl->addResource('Index:default');         // Zobrazenie hodnotenia
        $acl->allow('student', 'Index:default');

        $acl->addResource('Course:files');          // Zobrazenie suborov
        $acl->allow('student', 'Course:files');

        $acl->addResource('Index:timetable');       // Zobrazenie hodnotenia
        $acl->allow('student', 'Index:timetable');

        $acl->addResource('Event:detail');          // Zobrazenie terminu
        $acl->allow('guest', 'Event:detail');

        /* Lektor */
        $acl->addResource('Course:edit');           // Uprava kurzu, pozor na restricted inputka
        $acl->allow('lector', 'Course:edit');

        $acl->addResource('Event:edit');             // Uprava terminov, leader moze len upravovat
        $acl->allow('lector', 'Event:edit');

        /* Garant */
        $acl->addResource("ShowCourseStatus");
        $acl->allow("garant", "ShowCourseStatus");

        $acl->addResource("Course:create");
        $acl->allow("garant", "Course:create");

        $acl->addResource("Course:editData");
        $acl->allow("garant", "Course:editData");

        /* Leader */
        $acl->addResource("Room:default");
        $acl->allow("leader", "Room:default");

        $acl->addResource('Room:edit');             // Sprava miestnosti
        $acl->allow('leader', 'Room:edit');

        $acl->addResource("EditCourseStatus");
        $acl->allow("leader", "EditCourseStatus");

        /* Admin */
        $acl->addResource('User:default');          // Zoznam uzivatelov
        $acl->allow('admin', 'User:default');

        $acl->addResource('User:edit');             // Uprava uzivatela, uzivatelov
        $acl->allow('admin', 'User:edit');

        $acl->addResource('User:delete');             // Mazanie uzivatela, uzivatelov
        $acl->allow('admin', 'User:delete');

        return $acl;
    }
}