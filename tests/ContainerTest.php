<?php

namespace Yuloh\Container\Tests;

use Yuloh\Container\Container;
use Yuloh\Container\NotFoundException;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testContainer()
    {
        $container = new Container();

        $container->set('greet', function () {
            return 'hello world!';
        });

        $this->assertSame('hello world!', $container->get('greet'));
    }

    public function testContainerPassesSelfToClosure()
    {
        $container = new Container();

        $container->set('name', function () {
            return 'Matt';
        });

        $container->set('greet', function (Container $container) {
            $name = $container->get('name');
            return "Hello {$name}!";
        });

        $this->assertSame('Hello Matt!', $container->get('greet'));
    }

    public function testRemove()
    {
        $container = new Container();
        $container->set('name', function () {
            return 'Matt';
        });
        $this->assertTrue($container->has('name'));
        $container->remove('name');
        $this->assertFalse($container->has('name'));
    }

    public function testNotFound()
    {
        $this->setExpectedException(NotFoundException::class);
        $container = new Container();
        $container->get('unknown');
    }
}
