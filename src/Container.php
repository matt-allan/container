<?php

namespace Yuloh\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var \Closure[]
     */
    private $definitions = [];

    public function get($id)
    {
        if (!$this->has($id)) {
            throw NotFoundException::create($id);
        }

        return $this->definitions[$id]($this);
    }

    public function has($id)
    {
        return isset($this->definitions[$id]);
    }

    /**
     * Adds an entry to the container.
     *
     * @param string   $id       Identifier of the entry.
     * @param \Closure $value    The closure to invoke when this entry is resolved.
     *                           The closure will be given this container as the only
     *                           argument when invoked.
     */
    public function set($id, \Closure $value)
    {
        $this->definitions[$id] = function ($container) use ($value) {
            static $object;

            if (is_null($object)) {
                $object = $value($container);
            }

            return $object;
        };
    }
}
