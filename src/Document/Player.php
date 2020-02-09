<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Player
{
    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\Field(type="string")
     */
    private $login;

    /**
     * @MongoDB\Field(type="int")
     */
    private $nr;

    /**
     * @MongoDB\Field(type="int")
     */
    private $x;

    /**
     * @MongoDB\Field(type="int")
     */
    private $y;

    /**
     * @MongoDB\Field(type="int")
     */
    private $health;

    public function getId()
    {
        return $this->id;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getNr(): ?int
    {
        return $this->nr;
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function getY(): ?int
    {
        return $this->y;
    }

    public function getHealth(): ?int
    {
        return $this->health;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function setNr(int $nr): self
    {
        $this->nr = $nr;
        return $this;
    }

    public function setX(int $x): self
    {
        $this->x = $x;
        return $this;
    }

    public function setY(int $y): self
    {
        $this->y = $y;
        return $this;
    }

    public function setHealth(int $health): self
    {
        $this->health = $health;
        return $this;
    }
}
