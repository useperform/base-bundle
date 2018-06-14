<?php

namespace Perform\BaseBundle\Tests\Type;

use Perform\BaseBundle\FieldType\FieldTypeRegistry;
use Perform\BaseBundle\FieldType\DateType;
use Perform\BaseBundle\Config\FieldConfig;
use Perform\BaseBundle\Test\Services;
use Perform\BaseBundle\Crud\CrudRequest;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DateTypeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->registry = Services::fieldTypeRegistry([
            'date' => new DateType(),
        ]);
        $this->config = new FieldConfig($this->registry);
    }

    public function testListContextWithDefaults()
    {
        $obj = new \stdClass();
        $obj->date = new \DateTime();
        $this->config->add('date', [
            'type' => 'date',
        ]);
        $options = $this->config->getTypes(CrudRequest::CONTEXT_LIST)['date']['listOptions'];

        $expected = $obj->date->format('d/m/Y');
        $this->assertSame($expected, $this->registry->getType('date')->listContext($obj, 'date', $options));
    }

    public function testViewContextWithDefaults()
    {
        $obj = new \stdClass();
        $obj->date = new \DateTime();
        $this->config->add('date', [
            'type' => 'date',
        ]);
        $options = $this->config->getTypes(CrudRequest::CONTEXT_VIEW)['date']['viewOptions'];

        $expected = $obj->date->format('d/m/Y');
        $this->assertSame($expected, $this->registry->getType('date')->viewContext($obj, 'date', $options));
    }
}