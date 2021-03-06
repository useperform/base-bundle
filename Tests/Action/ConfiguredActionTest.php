<?php

namespace Perform\BaseBundle\Tests\Action;

use PHPUnit\Framework\TestCase;
use Perform\BaseBundle\Action\ActionInterface;
use Perform\BaseBundle\Action\ConfiguredAction;
use Perform\BaseBundle\Crud\CrudRequest;
use Perform\BaseBundle\Routing\CrudUrlGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * ConfiguredActionTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ConfiguredActionTest extends TestCase
{
    protected $action;

    public function setUp()
    {
        $this->action = $this->createMock(ActionInterface::class);
    }

    protected function stubRequest()
    {
        return $this->getMockBuilder(CrudRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetName()
    {
        $ca = new ConfiguredAction('foo', $this->action, []);
        $this->assertSame('foo', $ca->getName());
    }

    public function testGetLabel()
    {
        $options = [
            'label' => function($request, $entity) { return $entity->id; },
        ];
        $ca = new ConfiguredAction('foo', $this->action, $options);
        $entity = new \stdClass();
        $entity->id = 1;
        $this->assertSame(1, $ca->getLabel($this->stubRequest(), $entity));
    }

    public function testGetBatchLabel()
    {
        $options = [
            'batchLabel' => function() { return 'batch'; },
        ];
        $ca = new ConfiguredAction('foo', $this->action, $options);
        $this->assertSame('batch', $ca->getBatchLabel($this->stubRequest()));
    }

    public function testIsConfirmationRequired()
    {
        $options = [
            'confirmationRequired' => function() { return true; },
        ];
        $ca = new ConfiguredAction('foo', $this->action, $options);
        $this->assertTrue($ca->isConfirmationRequired());
    }

    public function testGetConfirmationMessage()
    {
        $options = [
            'label' => function() { return 'Remove'; },
            'confirmationMessage' => function($entity, $label) {
                return sprintf('%s %s?', $label, $entity->id);
            },
        ];
        $ca = new ConfiguredAction('foo', $this->action, $options);
        $entity = new \stdClass();
        $entity->id = 1;
        $this->assertSame('Remove 1?', $ca->getConfirmationMessage($this->stubRequest(), $entity));
    }

    public function testGetButtonStyle()
    {
        $ca = new ConfiguredAction('foo', $this->action, [
            'buttonStyle' => 'btn-danger'
        ]);
        $this->assertSame('btn-danger', $ca->getButtonStyle());
    }

    public function testLink()
    {
        $ca = new ConfiguredAction('something', $this->action, [
            'link' => function($entity) { return $entity->title; }
        ]);
        $entity = new \stdClass();
        $entity->title = 'hello';
        $this->assertSame('hello', $ca->getLink($entity, $this->createMock(CrudUrlGeneratorInterface::class), $this->createMock(UrlGeneratorInterface::class)));
    }

    public function testNotLink()
    {
        $ca = new ConfiguredAction('something', $this->action, []);
        $this->assertSame('', $ca->getLink(new \stdClass, $this->createMock(CrudUrlGeneratorInterface::class), $this->createMock(UrlGeneratorInterface::class)));
    }

    public function testRun()
    {
        $entities = [new \stdClass];
        $options = ['options'];
        $ca = new ConfiguredAction('foo', $this->action, []);

        $request = new CrudRequest('some_crud', CrudRequest::CONTEXT_ACTION);
        $this->action->expects($this->once())
            ->method('run')
            ->with($request, $entities, $options);

        $ca->run($request, $entities, $options);
    }
}
