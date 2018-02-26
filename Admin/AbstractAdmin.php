<?php

namespace Perform\BaseBundle\Admin;

use Perform\BaseBundle\Form\Type\AdminType;
use Perform\BaseBundle\Config\FilterConfig;
use Perform\BaseBundle\Config\ActionConfig;
use Perform\BaseBundle\Config\LabelConfig;
use Perform\BaseBundle\Config\ExportConfig;
use Perform\BaseBundle\Util\StringUtil;
use Doctrine\Common\Inflector\Inflector;
use Twig\Environment;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractAdmin implements AdminInterface
{
    protected $routePrefix;

    public function getFormType()
    {
        return AdminType::class;
    }

    public function getRoutePrefix()
    {
        if (!$this->routePrefix) {
            throw new \Exception('An admin route prefix must be configured.');
        }

        return $this->routePrefix;
    }

    public function getControllerName()
    {
        return 'Perform\BaseBundle\Controller\CrudController';
    }

    public function getActions()
    {
        return [
            '/' => 'list',
            '/view/{id}' => 'view',
            '/create' => 'create',
            '/edit/{id}' => 'edit',
        ];
    }

    public function configureFilters(FilterConfig $config)
    {
    }

    public function configureExports(ExportConfig $config)
    {
        $config->setFilename(str_replace(' ', '-', strtolower(Inflector::pluralize(StringUtil::adminClassToEntityName(static::class)))));
    }

    public function configureActions(ActionConfig $config)
    {
        $this->addViewAction($config);
        $this->addEditAction($config);
        $config->add('perform_base_delete');
    }

    protected function addViewAction(ActionConfig $config)
    {
        $config->addLink(function ($entity, $crudUrlGenerator) {
            return $crudUrlGenerator->generate($entity, 'view');
        },
            'View',
            [
                'isButtonAvailable' => function ($entity, $request) {
                    return $request->getContext() !== 'view';
                },
                'isGranted' => function ($entity, $authChecker) {
                    return $authChecker->isGranted('VIEW', $entity);
                },
                'buttonStyle' => 'btn-primary',
            ]);
    }

    protected function addEditAction(ActionConfig $config)
    {
        $config->addLink(function ($entity, $crudUrlGenerator) {
            return $crudUrlGenerator->generate($entity, 'edit');
        },
            'Edit',
            [
                'isGranted' => function ($entity, $authChecker) {
                    return $authChecker->isGranted('EDIT', $entity);
                },
                'buttonStyle' => 'btn-warning',
            ]);
    }

    public function configureLabels(LabelConfig $config)
    {
        $config->setEntityName(StringUtil::adminClassToEntityName(static::class))
            ->setEntityLabel(function ($entity) {
                return $entity->getId();
            });
    }

    public function getTemplate(Environment $twig, $entityName, $context)
    {
        //try a template in the entity bundle first, e.g.
        //@PerformContact/message/view.html.twig
        $template = StringUtil::crudTemplateForEntity($entityName, $context);

        return $twig->getLoader()->exists($template) ? $template : sprintf('@PerformBase/crud/%s.html.twig', $context);
    }
}
