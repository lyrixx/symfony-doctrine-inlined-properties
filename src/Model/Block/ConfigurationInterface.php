<?php

namespace App\Model\Block;

use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * ConfigurationInterface hold the static configuration of a block.
 *
 * This class MUST be serializable.
 */
#[DiscriminatorMap(typeProperty: 'type', mapping: [
    'image' => ImageBlock::class,
    'text' => TextBlock::class,
    'text_and_image' => TextAndImageBlock::class,
])]
interface ConfigurationInterface
{
}
