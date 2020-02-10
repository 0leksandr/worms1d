<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class User
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
    private $score;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $achievements;

    public function getId(): string
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getScore(): int
    {
        return $this->score ?? 0;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;
        return $this;
    }

    public function getAchievements(): array
    {
        return $this->achievements;
    }

    public function setAchievements(array $achievements): self
    {
        $this->achievements = $achievements;
        return $this;
    }
}
