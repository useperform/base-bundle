<?php

namespace Perform\BaseBundle\Action;

use Perform\BaseBundle\Admin\AdminRequest;

/**
 * Represents an action configured with options from admin classes.
 *
 * This class shouldn't be constructed manually; get one from
 * ActionConfig instead.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfiguredAction
{
    protected $name;
    protected $action;
    protected $options;

    public function __construct($name, ActionInterface $action, array $options)
    {
        $this->name = $name;
        $this->action = $action;
        $this->options = $options;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel(AdminRequest $request, $entity)
    {
        return call_user_func($this->options['label'], $request, $entity);
    }

    public function getBatchLabel(AdminRequest $request)
    {
        return call_user_func($this->options['batchLabel'], $request);
    }

    /**
     * Return true if this action is allowed to be run on the given entity.
     *
     * @param object $entity
     *
     * @return bool
     */
    public function isGranted($entity)
    {
        return (bool) $this->options['isGranted']($entity);
    }

    /**
     * Return true if the button for this action should be shown for this entity.
     * Note that this does not guarantee the action will be allowed on the entity.
     * The result of isGranted() will be used for that.
     *
     * @param AdminRequest $request
     * @param object       $entity
     *
     * @return bool
     */
    public function isButtonAvailable(AdminRequest $request, $entity)
    {
        return (bool) $this->options['isButtonAvailable']($request, $entity);
    }

    public function isBatchOptionAvailable(AdminRequest $request)
    {
        return (bool) $this->options['isBatchOptionAvailable']($request);
    }

    public function isConfirmationRequired()
    {
        return (bool) $this->options['confirmationRequired']();
    }

    /**
     * @return bool
     */
    public function isLink()
    {
        return isset($this->options['link']);
    }

    /**
     * @return string
     */
    public function getLink($entity)
    {
        return $this->isLink() ? $this->options['link']($entity) : '';
    }

    public function getConfirmationMessage(AdminRequest $request, $entity)
    {
        return $this->options['confirmationMessage']($entity, $this->getLabel($request, $entity));
    }

    public function getButtonStyle()
    {
        return $this->options['buttonStyle'];
    }

    public function run($entities, array $options = [])
    {
        return $this->action->run($entities, $options);
    }
}
