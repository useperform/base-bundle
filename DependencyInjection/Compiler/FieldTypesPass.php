<?php

namespace Perform\BaseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Perform\BaseBundle\Util\StringUtil;
use Perform\BaseBundle\DependencyInjection\LoopableServiceLocator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Setup field type services.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FieldTypesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $types = [];
        foreach ($container->findTaggedServiceIds('perform_base.field_type') as $service => $tag) {
            $alias = isset($tag[0]['alias']) ? $tag[0]['alias'] : $this->guessAlias($container, $service);
            if (isset($types[$alias])) {
                $existingService = (string) $types[$alias];
                $container->log($this, sprintf('Changing field type "%s" from service "%s" to service "%s". To avoid overriding this type, register "%s" with a different alias than "%s" in the "perform_base.field_type" tag.', $alias, $existingService, $service, $service, $alias));
            }

            $types[$alias] = new Reference($service);
        }

        $container->getDefinition('perform_base.field_type_registry')
            ->setArgument(0, LoopableServiceLocator::createDefinition($types));
    }

    public function guessAlias(ContainerBuilder $container, $service)
    {
        $class = $container->getDefinition($service)->getClass();

        return trim(strtolower(preg_replace('/([A-Z][a-z])/', '_\1', StringUtil::classBasename($class, 'Type'))), '_');
    }
}
