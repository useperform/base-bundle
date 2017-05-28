<?php

namespace Perform\BaseBundle\Type;

use Perform\BaseBundle\Exception\TypeNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TypeRegistry.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TypeRegistry
{
    protected $container;
    protected $classes = [];
    protected $instances = [];
    protected $services = [];
    protected $resolvers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addType($name, $classname)
    {
        $this->classes[$name] = $classname;
    }

    public function addTypeService($name, $service)
    {
        $this->services[$name] = $service;
    }

    public function getType($name)
    {
        //services have priority
        if (isset($this->services[$name])) {
            return $this->container->get($this->services[$name]);
        }

        if (!isset($this->classes[$name])) {
            throw new TypeNotFoundException(sprintf('Entity field type not found: "%s"', $name));
        }

        $classname = $this->classes[$name];
        if (!isset($this->instances[$name])) {
            $this->instances[$name] = new $classname();
        }

        return $this->instances[$name];
    }

    /**
     * Get a configured OptionsResolver for a given type.
     *
     * @return OptionsResolver
     */
    public function getOptionsResolver($name)
    {
        if (!isset($this->resolvers[$name])) {
            $resolver = new OptionsResolver();
            $resolver->setRequired('label');
            $resolver->setAllowedTypes('label', 'string');

            $this->getType($name)->configureOptions($resolver);
            $this->resolvers[$name] = $resolver;
        }

        return $this->resolvers[$name];
    }

    public function getAll()
    {
        $types = [];
        $keys = array_keys(array_merge($this->classes, $this->services));

        foreach ($keys as $key) {
            $types[$key] = $this->getType($key);
        }

        return $types;
    }
}
