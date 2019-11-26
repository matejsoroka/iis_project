<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security\Passwords;


/**
 * Users management.
 */
final class UserManager implements Nette\Security\IAuthenticator
{
    use Nette\SmartObject;

    private const
        TABLE_NAME = 'users',
        COLUMN_ID = 'id',
        COLUMN_USERNAME = 'username',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_EMAIL = 'email',
        COLUMN_ROLE = 'role',
        COLUMN_NAME = 'name',
        COLUMN_SURNAME = 'surname';


    /** @var Nette\Database\Context */
    private $database;

    /** @var Passwords */
    private $passwords;


    public function __construct(Nette\Database\Context $database, Passwords $passwords)
    {
        $this->database = $database;
        $this->passwords = $passwords;
    }


    /**
     * Performs an authentication.
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials): Nette\Security\IIdentity
    {
        [$username, $password] = $credentials;

        $row = $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_USERNAME, $username)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

        } elseif (!$this->passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

        } elseif ($this->passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update([
                self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
            ]);
        }

        $arr = $row->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
    }


    /**
     * @param string $username
     * @param string $name
     * @param string $surname
     * @param string $email
     * @param string $password
     * @throws DuplicateNameException
     * @throws Nette\Utils\AssertionException
     */
    public function add(string $username, string $name, string $surname, string $email, string $password): void
    {
        Nette\Utils\Validators::assert($email, 'email');
        try {
            $this->database->table(self::TABLE_NAME)->insert([
                self::COLUMN_USERNAME => $username,
                self::COLUMN_NAME => $name,
                self::COLUMN_SURNAME => $surname,
                self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
                self::COLUMN_EMAIL => $email,
                self::COLUMN_ROLE => 'registered'
            ]);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }

    /**
     * @param int $userId
     * @param string $name
     * @param string $surname
     * @param string $email
     * @param string $password
     * @throws DuplicateNameException
     * @throws Nette\Utils\AssertionException
     */
    public function edit(int $userId, string $name, string $surname, string $email, string $password): void
    {
        Nette\Utils\Validators::assert($email, 'email');
        try {
            $word = $password
                ? $this->passwords->hash($password)
                : $this->database->table(self::TABLE_NAME)->where('id', $userId)->fetch()->password;

            $this->database->table(self::TABLE_NAME)->where('id', $userId)
                ->update([
                self::COLUMN_NAME => $name,
                self::COLUMN_SURNAME => $surname,
                self::COLUMN_PASSWORD_HASH => $word,
                self::COLUMN_EMAIL => $email,
            ]);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }
}



class DuplicateNameException extends \Exception
{
}