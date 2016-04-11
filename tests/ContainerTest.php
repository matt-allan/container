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

    public function testShareOnlyCreatesOneInstance()
    {
        $container = new Container();

        $container->share('db', function ($c) {
            return new \stdClass();
        });

        $this->assertSame($container->get('db'), $container->get('db'));
    }

    public function testContainerPassesSelfToShareClosure()
    {
        $container = new Container();

        $container->share('name', function () {
            return 'Matt';
        });

        $container->share('greet', function (Container $container) {
            $name = $container->get('name');
            return "Hello {$name}!";
        });

        $this->assertSame('Hello Matt!', $container->get('greet'));
    }

    public function testArrayAccess()
    {
        $container = new Container();

        $container['db.config'] = function () {
            return 'mysql';
        };

        $this->assertSame('mysql', $container['db.config']);

        unset($container['db.config']);
        $this->assertFalse(isset($container['db.config']));
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
        $this->setExpectedException('Yuloh\Container\NotFoundException');
        $container = new Container();
        $container->get('unknown');
    }
}
