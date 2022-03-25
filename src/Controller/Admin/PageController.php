<?php

namespace App\Controller\Admin;

use App\Entity\Block;
use App\Entity\Page;
use App\Model\Block\ImageBlock;
use App\Model\Block\TextAndImageBlock;
use App\Model\Block\TextBlock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/admin/page/new', methods: ['GET'], name: 'admin_project_new')]
    public function new(): Response
    {
        $page = new Page();
        $page->setTitle('My page ' . uuid_create());

        $firstBlock = new Block();
        $firstBlock->setName('First block');
        $firstBlock->setConfiguration($c = new TextBlock());
        $c->content = '<p>Hello world!</p>';
        $page->addBlock($firstBlock);

        $secondBlock = new Block();
        $secondBlock->setName('second block');
        $secondBlock->setConfiguration($c = new ImageBlock());
        $c->src = 'https://jolicode.com/images/logo.svg';
        $c->alt = 'JoliCode logo';
        $page->addBlock($secondBlock);

        $thirdBlock = new Block();
        $thirdBlock->setName('third block');
        $thirdBlock->setConfiguration($c = new TextAndImageBlock());
        $c->text->content = '<p>Look at my nice picture!</p>';
        $c->image->src = 'https://jolicode.com/images/logo.svg';
        $c->image->alt = 'JoliCode logo';
        $page->addBlock($thirdBlock);

        $this->em->persist($page);
        $this->em->flush();

        return $this->redirectToRoute('page', ['id' => $page->getId()]);
    }

    #[Route('/admin/page/{id}/edit/{i}', methods: ['GET'], name: 'admin_page_edit')]
    public function edit(Page $page, int $i): Response
    {
        if (!\array_key_exists($i, $page->getBlocks()->toArray())) {
            throw $this->createNotFoundException("Block {$i} does not exist");
        }

        $configuration = $page->getBlocks()[$i]->getConfiguration();

        if ($configuration instanceof TextBlock) {
            $configuration->content = uuid_create();
        } elseif ($configuration instanceof ImageBlock) {
            if (random_int(0, 1)) {
                $configuration->src = 'https://redirection.io/media/logos/horizontal-logo-black-text-blue.png';
            } else {
                $configuration->src = 'https://jolicode.com/images/logo.svg';
            }
        } elseif ($configuration instanceof TextAndImageBlock) {
            $configuration->text->content = uuid_create();
        }

        $this->em->flush();

        return $this->redirectToRoute('page', ['id' => $page->getId()]);
    }

    #[Route('/admin/page/{id}/replace/{i}', methods: ['GET'], name: 'admin_page_replace')]
    public function replace(Page $page, int $i): Response
    {
        if (!\array_key_exists($i, $page->getBlocks()->toArray())) {
            throw $this->createNotFoundException("Block {$i} does not exist");
        }

        $configuration = new TextBlock();
        $configuration->content = uuid_create();

        $page->getBlocks()[$i]->setConfiguration($configuration);

        $this->em->flush();

        return $this->redirectToRoute('page', ['id' => $page->getId()]);
    }

    #[Route('/admin/page/{id}/nothing', methods: ['GET'], name: 'admin_page_nothing')]
    public function nothing(Page $page): Response
    {
        $this->em->flush();

        return $this->redirectToRoute('page', ['id' => $page->getId()]);
    }
}
