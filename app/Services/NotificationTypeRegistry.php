<?php

namespace App\Services;

use App\Booking\BookingServiceProvider;
use App\Posts\PostsServiceProvider;
use App\Properties\PropertiesServiceProvider;

class NotificationTypeRegistry
{
    protected ?array $types = null;

    private function loadTypes(): array
    {
        if ($this->types === null) {
            $all = [];
            foreach ([BookingServiceProvider::class, PropertiesServiceProvider::class, PostsServiceProvider::class] as $provider) {
                foreach ($provider::getNotificationTypes() as $key => $type) {
                    $all[$key] = $type;
                }
            }
            $this->types = $all;
        }

        return $this->types;
    }

    public function all(): array
    {
        return $this->loadTypes();
    }

    public function get(string $key): ?array
    {
        return $this->loadTypes()[$key] ?? null;
    }

    public function getByGroup(string $group): array
    {
        return array_filter($this->loadTypes(), fn ($type) => ($type['group'] ?? '') === $group);
    }

    public function groups(): array
    {
        $groups = [];
        foreach ($this->loadTypes() as $key => $type) {
            $group = $type['group'] ?? 'Other';
            if (! isset($groups[$group])) {
                $groups[$group] = [];
            }
            $groups[$group][$key] = $type;
        }

        return $groups;
    }
}
