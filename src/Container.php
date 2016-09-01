<?php

namespace Yuloh\Container;

use Interop\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;

class Container implements ContainerInterface, \ArrayAccess
{
    /**
     * @var []
     */
    private $definitions = [];

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws \Interop\Container\Exception\NotFoundException  No entry was found for this identifier.
     * @throws \Interop\Container\Exception\ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        // If we have a binding for it, then it's a closure.
        // We can just invoke it and return the resolved instance.
        if ($this->has($id)) {
            return $this->definitions[$id]($this);
        }

        // Otherwise we are going to try and use reflection to "autowire"
        // the dependencies and instantiate this entry if it's a class.
        if (!class_exists($id)) {
            throw NotFoundException::create($id);
        }

        $reflector = new ReflectionClass($id);

        // If the reflector is not instantiable, it's probably an interface.
        // In that case the user should register a factory, since we can't possibly know what
        // concrete class they want.  It could also be an abstract class, which we can't build either.
        if (!$reflector->isInstantiable()) {
            throw NotFoundException::create($id);
        }

        /** @var \ReflectionMethod|null */
        $constructor = $reflector->getConstructor();

        // If there isn't a constructor, there aren't any dependencies.
        // We can just instantiate the class and return it without doing anything.
        if (is_null($constructor)) {
            return new $id();
        }

        // Otherwise we need to go through and recursively build all of the dependencies.
        $dependencies = $constructor->getParameters();
        $dependencies = array_map(function (ReflectionParameter $dependency) use ($id) {

            if (is_null($dependency->getClass())) {
                throw NotFoundException::create($id);
            }

            return $this->get($dependency->getClass()->name);
        }, $dependencies);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return array_key_exists($id, $this->definitions);
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
        $this->definitions[$id] = $value;
    }

    /**
     * Adds a shared (singleton) entry to the container.
     *
     * @param string   $id       Identifier of the entry.
     * @param \Closure $value    The closure to invoke when this entry is resolved.
     *                           The closure will be given this container as the only
     *                           argument when invoked.
     */
    public function share($id, \Closure $value)
    {
        $this->definitions[$id] = function ($container) use ($value) {
            static $object;

            if (is_null($object)) {
                $object = $value($container);
            }

            return $object;
        };
    }

    /**
     * Removes an entry from the container.
     *
     * @param string $id Identifier of the entry to remove.
     *
     * @return void
     */
    public function remove($id)
    {
        if ($this->has($id)) {
            unset($this->definitions[$id]);
        }
    }

    /**
     * Whether a offset exists
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
