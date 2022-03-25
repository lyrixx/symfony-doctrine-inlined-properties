<?php

namespace App\Model\Block;

use Symfony\Component\Validator\Constraints as Assert;

final class TextBlock implements ConfigurationInterface
{
    #[Assert\NotBlank()]
    public $content;
}
