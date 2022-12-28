<?php

namespace App\Doctrine\Type;

use App\Model\Block\ConfigurationInterface;
use App\Model\Block\InvalidConfiguration;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BlockConfigurationType extends JsonType
{
    private static NormalizerInterface & DenormalizerInterface $serializer;

    // We use a public static method because doctrine does not let us use the constructor.
    public static function setSerializer(NormalizerInterface & DenormalizerInterface $serializer)
    {
        self::$serializer = $serializer;
    }

    /**
     * @param ConfigurationInterface $configuration
     */
    public function convertToDatabaseValue($configuration, AbstractPlatform $platform): string
    {
        $rawConfiguration = self::$serializer->normalize($configuration);

        return parent::convertToDatabaseValue($rawConfiguration, $platform);
    }

    /**
     * @param string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?ConfigurationInterface
    {
        $rawConfiguration = parent::convertToPHPValue($value, $platform);

        if (null === $rawConfiguration) {
            return null;
        }

        try {
            return self::$serializer->denormalize($rawConfiguration, ConfigurationInterface::class);
        } catch (NotNormalizableValueException) {
            return new InvalidConfiguration($rawConfiguration);
        }
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
