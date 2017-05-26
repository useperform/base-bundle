<?php

namespace Perform\BaseBundle\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Perform\BaseBundle\Exception\InvalidTypeException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as EntityFormType;

/**
 * EntityType.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EntityType extends AbstractType
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    public function createContext(FormBuilderInterface $builder, $field, array $options = [])
    {
        $builder->add($field, EntityFormType::class, [
            'class' => $options['class'],
            'choice_label' => $options['display_field'],
            'label' => $options['label'],
        ]);
    }

    public function getDefaultConfig()
    {
        return [
            'options' => [
                'display_field' => 'id',
            ],
        ];
    }

    public function listContext($entity, $field, array $options = [])
    {
        $relatedEntity = $this->accessor->getValue($entity, $field);
        $this->ensureEntity($field, $relatedEntity);

        return $this->accessor->getValue($relatedEntity, $options['display_field']);
    }

    protected function ensureEntity($field, $value)
    {
        if (!is_object($value) && !is_null($value)) {
            throw new InvalidTypeException(sprintf('The entity field "%s" passed to %s must be a doctrine entity, or null.', $field, __CLASS__));
        }
    }
}