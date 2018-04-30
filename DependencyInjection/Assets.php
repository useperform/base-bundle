<?php

namespace Perform\BaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add entries to various container parameters related to assets.
 *
 * Applications don't need to use this, they can define
 * perform_base.assets configuration nodes.
 *
 * These helpers are available to bundle extensions so they don't have
 * to implement PrependExtensionInterface to add to these nodes.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Assets
{
    const PARAM_NAMESPACES = 'perform_base.assets.namespaces';
    const PARAM_JS_MODULES = 'perform_base.assets.js_modules';
    const PARAM_ENTRYPOINTS = 'perform_base.assets.entrypoints';

    /**
     * Add an asset namespace to be used for `resolve.alias` in the webpack builds.
     */
    public static function addNamespace(ContainerBuilder $container, $namespace, $directory)
    {
        $existing = $container->hasParameter(self::PARAM_NAMESPACES) ? $container->getParameter(self::PARAM_NAMESPACES) : [];

        if (isset($existing[$namespace])) {
            throw new \Exception(sprintf('The asset namespace "%s" has already been registered.', $namespace));
        }

        $container->setParameter(self::PARAM_NAMESPACES, array_merge($existing, [
            $namespace => $directory,
        ]));
    }

    /**
     * Add a javascript module. Exported functions in the imported
     * file will be added to the global window.Perform object under
     * the supplied name.
     *
     * e.g. 'myApp' becomes 'Perform.myApp'
     *
     * @param string $name   The name of the module
     * @param string $import The file to import. It should export a javascript object containing the Perform module
     */
    public static function addJavascriptModule(ContainerBuilder $container, $name, $import)
    {
        $existing = $container->hasParameter(self::PARAM_JS_MODULES) ? $container->getParameter(self::PARAM_JS_MODULES) : [];

        if (isset($existing[$name])) {
            throw new \Exception(sprintf('The javascript module "%s" has already been registered.', $name));
        }

        $container->setParameter(self::PARAM_JS_MODULES, array_merge($existing, [
            $name => $import,
        ]));
    }

    /**
     * Add a standalone asset entrypoint.
     *
     * @param string|array $path
     */
    public static function addEntryPoint(ContainerBuilder $container, $name, $path)
    {
        $path = (array) $path;
        $existing = $container->hasParameter(self::PARAM_ENTRYPOINTS) ? $container->getParameter(self::PARAM_ENTRYPOINTS) : [];

        if (isset($existing[$name])) {
            throw new \Exception(sprintf('The asset entrypoint "%s" has already been registered.', $name));
        }

        $container->setParameter(self::PARAM_ENTRYPOINTS, array_merge($existing, [
            $name => $path,
        ]));
    }
}
