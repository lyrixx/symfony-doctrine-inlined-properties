<?php

namespace App\Model\Block;

use Symfony\Component\Validator\Constraints as Assert;

final class TextAndImageBlock implements ConfigurationInterface
{
    #[Assert\NotBlank()]
    #[Assert\Valid()]
    public TextBlock $text;

    #[Assert\NotBlank()]
    #[Assert\Image()]
    public ImageBlock $image;

    public function __construct()
    {
        $this->text = new TextBlock();
        $this->image = new ImageBlock();
    }
}
