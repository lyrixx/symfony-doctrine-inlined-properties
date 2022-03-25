<?php

namespace App\Entity;

use App\Doctrine\Model\HasInlinedProperties;
use App\Model\Block\ConfigurationInterface;
use App\Repository\BlockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
class Block implements HasInlinedProperties
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'guid')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'block_configuration')]
    private $configuration;

    #[ORM\ManyToOne(targetEntity: page::class, inversedBy: 'blocks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $page;

    public function __construct()
    {
        $this->id = uuid_create();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getPage(): ?page
    {
        return $this->page;
    }

    public function setPage(?page $page): void
    {
        $this->page = $page;
    }

    public function getInlinedProperties(): array
    {
        return ['configuration'];
    }
}
