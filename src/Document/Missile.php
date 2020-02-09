<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Missile
{
    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\Field(type="string")
     */
    private $matchId;

    /**
     * @MongoDB\Field(type="int")
     */
    private $shooterNr;

    /**
     * @MongoDB\Field(type="int")
     */
    private $time;

    /**
     * @MongoDB\Field(type="float")
     */
    private $sx;

    /**
     * @MongoDB\Field(type="float")
     */
    private $sy;

    public function getId()
    {
        return $this->id;
    }

    public function getMatchId()
    {
        return $this->matchId;
    }

    public function setMatchId(string $matchId): self
    {
        $this->matchId = $matchId;
        return $this;
    }

    public function getShooterNr(): int
    {
        return $this->shooterNr;
    }

    public function setShooterNr(int $shooterNr): self
    {
        $this->shooterNr = $shooterNr;
        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): self
    {
        $this->time = $time;
        return $this;
    }

    public function getSx(): float
    {
        return $this->sx;
    }

    public function setSx(float $sx): self
    {
        $this->sx = $sx;
        return $this;
    }

    public function getSy(): float
    {
        return $this->sy;
    }

    public function setSy(float $sy): self
    {
        $this->sy = $sy;
        return $this;
    }
}
