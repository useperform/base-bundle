<?php

namespace Perform\BaseBundle\FieldType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType as FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Perform\BaseBundle\Asset\AssetContainer;

/**
 * Use the ``html`` type for entity properties storing html.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class HtmlType extends AbstractType
{
    protected $assets;

    public function __construct(AssetContainer $assets)
    {
        $this->assets = $assets;
    }

    public function createContext(FormBuilderInterface $builder, $field, array $options = [])
    {
        $this->assets->addCss('https://cdn.quilljs.com/1.3.5/quill.snow.css');
        $this->assets->addJs('https://cdn.quilljs.com/1.3.5/quill.js');
        $this->assets->addJs('/bundles/performbase/js/types/html.js');
        $formOptions = [
            'label' => $options['label'],
        ];
        $builder->add($field, FormType::class, array_merge($formOptions, $options['form_options']));

        return [
            'html' => $this->getPropertyAccessor()->getValue($builder->getData(), $field),
        ];
    }

    public function getDefaultConfig()
    {
        return [
            'template' => '@PerformBase/field_type/html.html.twig',
        ];
    }
}
