<?php

namespace App\Model\Block;

use Symfony\Component\Validator\Constraints as Assert;

final class ImageBlock implements ConfigurationInterface
{
    #[Assert\NotBlank()]
    #[Assert\Url()]
    public $src;

    #[Assert\NotBlank()]
    public $alt;
}
