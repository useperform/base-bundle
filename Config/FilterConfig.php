<?php

namespace Perform\BaseBundle\Config;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Perform\BaseBundle\Util\StringUtil;
use Perform\BaseBundle\Filter\Filter;

/**
 * FilterConfig.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FilterConfig
{
    protected $resolver;
    protected $filters = [];
    protected $defaultFilter;

    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver
            ->setRequired(['query'])
            ->setAllowedTypes('query', 'callable')
            ->setDefined('label')
            ->setAllowedTypes('label', 'string')
            ->setDefault('count', false)
            ->setAllowedTypes('count', 'boolean')
            ;
    }

    public function getFilter($name)
    {
        return isset($this->filters[$name]) ? $this->filters[$name] : null;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function add($name, array $options)
    {
        if (!isset($options['label'])) {
            $options['label'] = StringUtil::sensible($name);
        }
        $this->filters[$name] = new Filter($this->resolver->resolve($options));

        return $this;
    }

    /**
     * @param string $filter
     *
     * @return FilterConfig
     */
    public function setDefault($filter)
    {
        $this->defaultFilter = $filter;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefault()
    {
        return $this->defaultFilter;
    }
}
