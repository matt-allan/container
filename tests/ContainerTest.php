<?php

namespace Yuloh\Container\Tests;

use Yuloh\Container\Container;

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

    public function testContainerOnlyCreatesOneInstance()
    {
        $container = new Container();

        $container->set('db', function ($c) {
            return new \stdClass();
        });

        $this->assertSame($container->get('db'), $container->get('db'));
    }

    public function testNotFound()
    {
        $this->setExpectedException('Yuloh\Container\NotFoundException');
        $container = new Container();
        $container->get('unknown');
    }
}
