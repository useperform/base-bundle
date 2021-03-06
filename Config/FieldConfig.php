<?php

namespace Perform\BaseBundle\Config;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Perform\BaseBundle\Util\StringUtil;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Perform\BaseBundle\Exception\InvalidFieldException;
use Perform\BaseBundle\FieldType\FieldTypeRegistry;
use Perform\BaseBundle\Crud\CrudRequest;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FieldConfig
{
    protected static $optionKeys = [
        'listOptions',
        'viewOptions',
        'createOptions',
        'editOptions',
        'exportOptions',
    ];

    protected $resolver;
    protected $fields = [];
    protected $addedConfigs = [];
    protected $defaultContexts = [
        CrudRequest::CONTEXT_LIST,
        CrudRequest::CONTEXT_VIEW,
        CrudRequest::CONTEXT_CREATE,
        CrudRequest::CONTEXT_EDIT,
        CrudRequest::CONTEXT_EXPORT,
    ];
    protected $defaultContextsChanged = false;
    protected $defaultSort;

    public function __construct(FieldTypeRegistry $registry)
    {
        $this->registry = $registry;
        $this->configureOptionsResolver();
    }

    protected function configureOptionsResolver()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver
            ->setRequired(['type'])
            ->setDefaults([
                'contexts' => [],
                'sort' => true,
            ])
            ->setAllowedTypes('contexts', 'array')
            ->setDefault('template', '@PerformBase/field_type/simple.html.twig')
            ->setAllowedTypes('template', 'string')
            ->setDefined(static::$optionKeys);
        foreach (static::$optionKeys as $key) {
            $this->resolver->setAllowedTypes($key, 'array');
        }
    }

    public function getTypes($context)
    {
        $types = [];
        foreach ($this->fields as $field => $config) {
            if (!in_array($context, $config['contexts'])) {
                continue;
            }

            $types[$field] = $this->fields[$field];
        }

        return $types;
    }

    public function getAllTypes()
    {
        return $this->fields;
    }

    public function getAddedConfigs()
    {
        return $this->addedConfigs;
    }

    /**
     * Add or amend a field.
     * If the field is already registered, the config will be merged.
     * If the field is not registered, the config will be merged with
     * the default config for that type.
     *
     * @param string $name   The name of the field
     * @param array  $config Configuration for this field
     */
    public function add($name, array $config)
    {
        $this->addedConfigs[$name][] = $config;

        //make sure a label exists
        //only do this the first time to prevent nuking a custom label
        //with an override that doesn't have a label
        if (!isset($config['options']['label']) && !isset($this->fields[$name])) {
            $config['options']['label'] = StringUtil::sensible($name);
        }

        $this->normaliseOptions($config);

        // use the default for the type if there is no existing config
        if (!isset($this->fields[$name])) {
            if (!isset($config['type'])) {
                throw new InvalidFieldException('FieldConfig#add() requires "type" to be set.');
            }
            $this->fields[$name] = $this->registry->getType($config['type'])->getDefaultConfig();

            // set default contexts if the field type didn't provide
            // any with its default config, or if the default contexts
            // have been explicitly changed
            if (!isset($this->fields[$name]['contexts']) || $this->defaultContextsChanged) {
                $this->fields[$name]['contexts'] = $this->defaultContexts;
            }

            $this->normaliseOptions($this->fields[$name]);
        }

        $existingConfig = $this->fields[$name];

        //replace the entire contexts array in the existing config if
        //they are given
        if (isset($config['contexts'])) {
            unset($existingConfig['contexts']);
        }

        //merge with the existing config
        $config = array_replace_recursive($existingConfig, $config);

        //resolve the top level options
        $resolved = $this->resolver->resolve($config);

        //resolve the options for each context using the resolver from the type
        $this->fields[$name] = $this->resolveContextOptions($name, $resolved);

        return $this;
    }

    /**
     * Ensure the options for each context are set, and remove
     * 'options' itself.
     */
    protected function normaliseOptions(&$config)
    {
        if (!isset($config['options'])) {
            $config['options'] = [];
        }
        foreach (static::$optionKeys as $key) {
            $config[$key] = isset($config[$key]) ?
                          array_merge($config['options'], $config[$key]) :
                          $config['options'];
        }
        unset($config['options']);
    }

    protected function resolveContextOptions($name, array $config)
    {
        $resolver = $this->registry->getOptionsResolver($config['type']);
        foreach (static::$optionKeys as $key) {
            try {
                $config[$key] = $resolver->resolve($config[$key]);
            } catch (ExceptionInterface $e) {
                $msg = sprintf('"%s" for the field "%s" of type "%s" are invalid: %s', $key, $name, $config['type'], $e->getMessage());
                throw new InvalidFieldException($msg, 1, $e);
            }
        }

        return $config;
    }

    /**
     * @param string $name
     * @param string $direction
     *
     * @return FieldConfig
     */
    public function setDefaultSort($name, $direction)
    {
        $direction = strtoupper($direction);
        if ($direction !== 'ASC' && $direction !== 'DESC') {
            throw new \InvalidArgumentException('Default sort direction must be "ASC" or "DESC"');
        }

        $this->defaultSort = [$name, $direction];

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultSort()
    {
        if (!$this->defaultSort) {
            $this->defaultSort = [null, 'ASC'];
        }

        return $this->defaultSort;
    }

    /**
     * Set the default contexts for added fields.
     *
     * The new defaults will only apply to add() invocations after
     * this method has been called.
     *
     * You may call this method multiple times.
     * Each call to add() will use the latest given defaults.
     *
     * @param array $contexts
     */
    public function setDefaultContexts(array $contexts)
    {
        $this->defaultContexts = $contexts;
        $this->defaultContextsChanged = true;

        return $this;
    }
}
