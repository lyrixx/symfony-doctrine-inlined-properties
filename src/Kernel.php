<?php

namespace App;

use App\Doctrine\Type\BlockConfigurationType;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot()
    {
        parent::boot();

        BlockConfigurationType::setSerializer($this->getContainer()->get('serializer_doctrine'));
    }
}
