<?php

declare(strict_types=1);

namespace FoodOS\Core;

final class SystemArchitecture
{
    /**
     * @param list<Module> $modules
     */
    public function __construct(private array $modules)
    {
    }

    /**
     * @return array<string, array{responsibilities:list<string>, integrations:list<string>}>
     */
    public function describe(): array
    {
        $result = [];

        foreach ($this->modules as $module) {
            $result[$module->name()] = [
                'responsibilities' => $module->responsibilities(),
                'integrations' => $module->integrations(),
            ];
        }

        return $result;
    }
}
