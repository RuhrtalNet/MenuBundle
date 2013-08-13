<?php

namespace RuhrtalNet\MenuBundle;

use RuhrtalNet\MenuBundle\DependencyInjection\Compiler\MenuTagCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MenuBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MenuTagCompilerPass());
    }
}
