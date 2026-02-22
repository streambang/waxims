<?php

declare(strict_types=1);

namespace FoodOS\Core;

interface Module
{
    public function name(): string;

    /**
     * @return list<string>
     */
    public function responsibilities(): array;

    /**
     * @return list<string>
     */
    public function integrations(): array;
}
