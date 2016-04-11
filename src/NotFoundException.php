<?php

namespace Yuloh\Container;

class NotFoundException extends \Exception implements \Interop\Container\Exception\NotFoundException
{
    /**
     * Creates a new NotFoundException.
     *
     * @param string $id The ID of the entry that was not found.
     *
     * @return \Yuloh\Container\NotFoundException
     */
    public static function create($id)
    {
        return new self(sprintf('No container definition was found for "%s"', $id));
    }
}
