<?php

declare(strict_types=1);

namespace FoodOS\Core;

abstract class AbstractModule implements Module
{
    /**
     * @return list<string>
     */
    abstract protected function scope(): array;

    /**
     * @return list<string>
     */
    abstract protected function dependencyContracts(): array;

    public function responsibilities(): array
    {
        return $this->scope();
    }

    public function integrations(): array
    {
        return $this->dependencyContracts();
    }
}
