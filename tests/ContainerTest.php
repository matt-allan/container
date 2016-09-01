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

    public function testContainerCanAutowireRecursively()
    {
        $container = new Container();
        $container->set(LoggerInterface::class, function () {
            return new Logger();
        });

        $testController = $container->get(TestController::class);

        $this->assertInstanceOf(UserRepository::class, $testController->userRepo);
        $this->assertInstanceOf(DB::class, $testController->userRepo->db);
        $this->assertInstanceOf(Logger::class, $testController->userRepo->db->logger);
    }
}

class TestController
{
    public $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }
}

class UserRepository
{
    public $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }
}

class DB
{
    public $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

interface LoggerInterface
{
    public function log($msg);
}

class Logger implements LoggerInterface
{
    public function log($msg){}
}
