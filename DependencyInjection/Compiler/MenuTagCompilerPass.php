<?php
/**
 * This file is part of the RuhrtalNet\MenuBundle.
 *
 * @version $Revision$
 */

namespace RuhrtalNet\MenuBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 */
class MenuTagCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('RuhrtalNet.MenuBundle.MenuBuilder')) {
            return;
        }

        $definition = $container->getDefinition(
            'RuhrtalNet.MenuBundle.MenuBuilder'
        );

        $taggedServices = $container->findTaggedServiceIds('RuhrtalNet.MenuItem');
        foreach (array_keys($taggedServices) as $id) {
            $definition->addMethodCall(
                'addMenuItem',
                array(new Reference($id))
            );
        }
    }
}
