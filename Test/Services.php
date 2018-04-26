<?php

namespace Perform\BaseBundle\Test;

use Perform\BaseBundle\DependencyInjection\LoopableServiceLocator;
use Perform\BaseBundle\Type\TypeRegistry;

/**
 * Helpers to create difficult to construct services for testing.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Services
{
    public static function typeRegistry(array $services)
    {
        return new TypeRegistry(self::serviceLocator($services));
    }

    public static function serviceLocator(array $services)
    {
        $factories = [];
        foreach ($services as $alias => $service) {
            $factories[$alias] = function () use ($service) { return $service; };
        }

        return new LoopableServiceLocator($factories);
    }
}
