<?php

namespace Platine\Framework\Demo\Form\Param;

use Platine\Orm\Entity;

class UserParam extends BaseParam
{
    protected string $username = '';
    protected string $lastname = '';
    protected string $firstname = '';
    protected string $password = '';
    protected string $age = '';

    public function fromEntity(Entity $entity): UserParam
    {
        $this->username = $entity->username;
        $this->lastname = $entity->lastname;
        $this->firstname = $entity->firstname;
        $this->password = $entity->password;
        $this->age = $entity->age;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getAge(): string
    {
        return $this->age;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function setAge(string $age): self
    {
        $this->age = $age;
        return $this;
    }
}
