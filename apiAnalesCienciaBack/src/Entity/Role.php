<?php

/**
 * PHP version 7.4
 * src/Entity/Role.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Entity;

use JsonSerializable;
use OutOfRangeException;

/**
 * Class Role
 */
class Role implements JsonSerializable
{
    // scope names
    public const ROLE_READER = 'reader';
    public const ROLE_WRITER = 'writer';
    public const ROLES = [
        0 => self::ROLE_READER,
        1 => self::ROLE_WRITER,
    ];

    private int $role;

    /**
     * Role constructor.
     * @param string $role
     * @throws OutOfRangeException
     */
    public function __construct(string $role = self::ROLE_READER)
    {
        $role = strtolower($role);
        if (!in_array($role, self::ROLES, true)) {
            throw new OutOfRangeException('Role out of range');
        }
        $this->role = (int) array_search($role, self::ROLES, true);
    }

    /**
     * @param string $role
     * @return Role
     */
    public function setRole(string $role): self
    {
        $role = strtolower($role);
        if (!in_array($role, self::ROLES, true)) {
            throw new OutOfRangeException('Role out of range');
        }
        $this->role = (int) array_search($role, self::ROLES, true);
        return $this;
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        $role = strtolower($role);
        if (self::ROLE_READER === $role) {
            return true;
        }

        if (self::ROLE_WRITER === $role) {
            return self::ROLES[$this->role] === self::ROLE_WRITER;
        }

        return false;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link https://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString(): string
    {
        return (string) self::ROLES[$this->role];
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'role' => $this->__toString()
            ];
    }
}
