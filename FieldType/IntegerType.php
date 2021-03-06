<?php

namespace Perform\BaseBundle\FieldType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType as IntegerFormType;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class IntegerType extends AbstractType
{
    public function createContext(FormBuilderInterface $builder, $field, array $options = [])
    {
        $formOptions = [
            'label' => $options['label'],
        ];
        $builder->add($field, IntegerFormType::class, array_merge($formOptions, $options['form_options']));
    }
}
