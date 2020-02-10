<?php
namespace SymfonyExtra\DoctrineBundle;

use SymfonyExtra\DoctrineBundle\DependencyInjection\ConfigExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SymfonyExtraDoctrineBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ConfigExtension();
    }

}