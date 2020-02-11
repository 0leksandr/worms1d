<?php

namespace App\Entity\Achievement;

use App\Document\Match;
use App\Document\User;
use JsonSerializable;

abstract class Achievement implements JsonSerializable
{
    abstract public static function fromJson(string $json): self;

    abstract public function match(Match $match, User $winner): bool;
}
