<?php

namespace App\Entity\Achievement;

use JsonSerializable;

abstract class Achievement implements JsonSerializable
{
    abstract public static function fromJson(string $json): self;
}
