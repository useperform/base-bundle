<?php

namespace Perform\BaseBundle\Tests\Action;

use Perform\BaseBundle\Action\ActionRegistry;
use Perform\BaseBundle\Action\ActionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Perform\BaseBundle\Action\ActionNotFoundException;

/**
 * ActionRegistryTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ActionRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    protected $registry;

    public function setUp()
    {
        $this->container = $this->getMock(ContainerInterface::class);
        $this->registry = new ActionRegistry($this->container);
    }

    public function testGetAction()
    {
        $this->registry->addAction('foo_action', 'Foo', 'foo_service');
        $action = $this->getMock(ActionInterface::class);
        $this->container->expects($this->any())
            ->method('get')
            ->with('foo_service')
            ->will($this->returnValue($action));

        $this->assertSame($action, $this->registry->getAction('foo_action'));
    }

    public function testGetUnknownService()
    {
        $this->setExpectedException(ActionNotFoundException::class);
        $this->registry->getAction('bar_action');
    }

    public function testGetActionsForEntityClass()
    {
        $this->assertSame([], $this->registry->getActionsForEntityClass('Foo'));

        $this->registry->addAction('foo_action', 'Foo', 'foo_service');
        $this->registry->addAction('bar_action', 'Foo', 'bar_service');
        $fooAction = $this->getMock(ActionInterface::class);
        $barAction = $this->getMock(ActionInterface::class);
        $this->container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('foo_service')],
                [$this->equalTo('bar_service')]
            )
            ->will($this->onConsecutiveCalls($fooAction, $barAction));

        $expected = [
            'foo_action' => $fooAction,
            'bar_action' => $barAction,
        ];
        $this->assertSame($expected, $this->registry->getActionsForEntityClass('Foo'));
    }
}