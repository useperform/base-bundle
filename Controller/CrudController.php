<?php

namespace Perform\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Perform\BaseBundle\Crud\CrudRequest;
use Perform\BaseBundle\Twig\Extension\ActionExtension;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CrudController extends Controller
{
    protected $entity;

    protected function initialize(CrudRequest $crudRequest)
    {
        $this->entity = $this->get('perform_base.doctrine.entity_resolver')->resolve($crudRequest->getEntityClass());
        // tell crudRequest about the resolved entity
        $crudRequest->setEntityClass($this->entity);
        $this->get('twig')
            ->getExtension(ActionExtension::class)
            ->setCrudRequest($crudRequest);
    }

    /**
     * @return CrudInterface
     */
    protected function getCrud()
    {
        return $this->get('perform_base.crud.registry')
            ->get($this->entity);
    }

    protected function getTypeConfig()
    {
        return $this->get('perform_base.config_store')
            ->getTypeConfig($this->entity);
    }

    protected function getFilterConfig()
    {
        return $this->get('perform_base.config_store')
            ->getFilterConfig($this->entity);
    }

    protected function getActionConfig()
    {
        return $this->get('perform_base.config_store')
            ->getActionConfig($this->entity);
    }

    protected function getLabelConfig()
    {
        return $this->get('perform_base.config_store')
            ->getLabelConfig($this->entity);
    }

    protected function newEntity()
    {
        $className = $this->getDoctrine()
                   ->getManager()
                   ->getClassMetadata($this->entity)
                   ->name;

        return new $className();
    }

    protected function throwNotFoundIfNull($entity, $identifier)
    {
        if (!$entity) {
            throw new NotFoundHttpException(sprintf('Entity with identifier "%s" was not found.', $identifier));
        }
    }

    protected function findDefaultEntity()
    {
        $repo = $this->getDoctrine()->getRepository($this->entity);
        $result = $repo->findBy([], [], 1);
        if (!isset($result[0])) {
            throw new NotFoundHttpException();
        }

        return $result[0];
    }

    private function setFormTheme($formView)
    {
        $this->get('twig')
            ->getExtension(FormExtension::class)
            ->renderer->setTheme($formView, '@PerformBase/form_theme.html.twig');
    }

    public function listAction(Request $request)
    {
        $crudRequest = CrudRequest::fromRequest($request, CrudRequest::CONTEXT_LIST);
        $this->initialize($crudRequest);
        list($paginator, $orderBy) = $this->get('perform_base.selector.entity')->listContext($crudRequest);
        $populator = $this->get('perform_base.template_populator');

        return $populator->listContext($crudRequest, $paginator, $orderBy);
    }

    public function viewAction(Request $request, $id)
    {
        $crudRequest = CrudRequest::fromRequest($request, CrudRequest::CONTEXT_VIEW);
        $this->initialize($crudRequest);
        $entity = $this->get('perform_base.selector.entity')->viewContext($crudRequest, $id);
        $this->throwNotFoundIfNull($entity, $id);
        $this->denyAccessUnlessGranted('VIEW', $entity);
        $populator = $this->get('perform_base.template_populator');

        return $populator->viewContext($crudRequest, $entity);
    }

    public function viewDefaultAction(Request $request)
    {
        $this->initialize(CrudRequest::fromRequest($request, CrudRequest::CONTEXT_VIEW));

        return $this->viewAction($request, $this->findDefaultEntity()->getId());
    }

    public function createAction(Request $request)
    {
        $crudRequest = CrudRequest::fromRequest($request, CrudRequest::CONTEXT_CREATE);
        $this->initialize($crudRequest);
        $builder = $this->createFormBuilder($entity = $this->newEntity());
        $crud = $this->getCrud();
        $form = $this->createForm($crud->getFormType(), $entity, [
            'entity' => $this->entity,
            'context' => $crudRequest->getContext(),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->get('perform_base.entity_manager')->create($crudRequest, $entity);
                $this->addFlash('success', 'Item created successfully.');

                return $this->redirect($this->get('perform_base.routing.crud_url')->generateDefaultEntityRoute($entity));
            } catch (\Exception $e) {
                $this->addFlash('danger', 'An error occurred.');
            }
        }

        $formView = $form->createView();
        $this->setFormTheme($formView);
        $populator = $this->get('perform_base.template_populator');

        return $populator->editContext($crudRequest, $formView, $entity);
    }

    public function editAction(Request $request, $id)
    {
        $crudRequest = CrudRequest::fromRequest($request, CrudRequest::CONTEXT_EDIT);
        $this->initialize($crudRequest);
        $entity = $this->get('perform_base.selector.entity')->editContext($crudRequest, $id);
        $this->throwNotFoundIfNull($entity, $id);
        $this->denyAccessUnlessGranted('EDIT', $entity);
        $crud = $this->getCrud();
        $form = $this->createForm($crud->getFormType(), $entity, [
            'entity' => $this->entity,
            'context' => $crudRequest->getContext(),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->get('perform_base.entity_manager')->update($crudRequest, $entity);
                $this->addFlash('success', 'Item updated successfully.');

                return $this->redirect($this->get('perform_base.routing.crud_url')->generateDefaultEntityRoute($entity));
            } catch (\Exception $e) {
                $this->addFlash('danger', 'An error occurred.');
            }
        }

        $formView = $form->createView();
        $this->setFormTheme($formView);
        $populator = $this->get('perform_base.template_populator');

        return $populator->editContext($crudRequest, $formView, $entity);
    }

    public function editDefaultAction(Request $request)
    {
        $this->initialize(CrudRequest::fromRequest($request, CrudRequest::CONTEXT_EDIT));

        return $this->editAction($request, $this->findDefaultEntity()->getId());
    }
}
