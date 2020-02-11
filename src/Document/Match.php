<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Match
{
    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\EmbedOne(targetDocument="App\Document\Player")
     */
    private $player1;

    /**
     * @MongoDB\EmbedOne(targetDocument="App\Document\Player")
     */
    private $player2;

    /**
     * @MongoDB\Field(type="int")
     */
    private $player1Health;

    /**
     * @MongoDB\Field(type="int")
     */
    private $player2Health;

    public function getId(): string
    {
        return $this->id;
    }

    public function getPlayer1(): Player
    {
        return $this->player1;
    }

    public function getPlayer2(): Player
    {
        return $this->player2;
    }

    public function setPlayer1(Player $player1): self
    {
        $this->player1 = $player1;
        return $this;
    }

    public function setPlayer2(Player $player2): self
    {
        $this->player2 = $player2;
        return $this;
    }

    public function getPlayer1Health(): int
    {
        return $this->player1Health;
    }

    public function getPlayer2Health(): int
    {
        return $this->player2Health;
    }

    public function setPlayer1Health(int $player1Health): self
    {
        $this->player1Health = $player1Health;
        return $this;
    }

    public function setPlayer2Health(int $player2Health): self
    {
        $this->player2Health = $player2Health;
        return $this;
    }
}
