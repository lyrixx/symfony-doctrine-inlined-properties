<?php

namespace App\Doctrine\Model;

interface HasInlinedProperties
{
    public function getInlinedProperties(): array;
}
