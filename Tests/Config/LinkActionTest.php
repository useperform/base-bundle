<?php

namespace Perform\BaseBundle\Tests\Config;

use Perform\BaseBundle\Action\LinkAction;
use Perform\BaseBundle\Admin\AdminRequest;
use Perform\BaseBundle\Config\ActionConfig;
use Perform\BaseBundle\Action\ActionRegistry;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class LinkActionTest extends \PHPUnit_Framework_TestCase
{
    protected $action;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder(ActionRegistry::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->config = new ActionConfig($this->registry);
        $this->config->addInstance('link', new LinkAction());
        $this->action = $this->config->get('link');
    }

    public function testRunIsForbidden()
    {
        $this->setExpectedException(\RuntimeException::class);
        $this->action->run([], []);
    }

    public function testBatchActionDisabled()
    {
        $request = $this->getMockBuilder(AdminRequest::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        $this->assertFalse($this->action->isBatchOptionAvailable($request));
    }

    public function testIsAlwaysGranted()
    {
        $this->assertTrue($this->action->isGranted(new \stdClass()));
    }
}
