<?php

namespace App\Services;

use App\Blog\BlogServiceProvider;
use App\Shop\ShopServiceProvider;

class FeatureRegistry
{
    /**
     * @var array<int, ShopServiceProvider|BlogServiceProvider>
     */
    protected array $providers;

    public function __construct()
    {
        $this->providers = [
            app(ShopServiceProvider::class),
            app(BlogServiceProvider::class),
        ];
    }

    /**
     * @return array<int, ShopServiceProvider|BlogServiceProvider>
     */
    public function all(): array
    {
        return $this->providers;
    }

    public function get(string $slug): ?object
    {
        foreach ($this->providers as $provider) {
            if ($provider->slug() === $slug) {
                return $provider;
            }
        }

        return null;
    }

    public function cleanupUserData(int $userId): void
    {
        foreach ($this->providers as $provider) {
            $provider->onUserDelete($userId);
        }
    }

    public function getAllNavItems(): array
    {
        $items = [];

        foreach ($this->providers as $provider) {
            $nav = $provider->getNavItems();
            if (! empty($nav)) {
                $nav['groupLabel'] = $provider->getNavGroupLabel();
                $items[] = $nav;
            }
        }

        return $items;
    }

    public function getAllSharedData(): array
    {
        $data = [];

        foreach ($this->providers as $provider) {
            $shared = $provider->getSharedData();
            if (! empty($shared)) {
                $data = array_merge($data, $shared);
            }
        }

        return $data;
    }

    public function getAllMetaFormattingConfigs(): array
    {
        $configs = [];

        foreach ($this->providers as $provider) {
            $config = $provider->getMetaFormattingConfig();
            if (! empty($config)) {
                $configs[$provider->slug()] = $config;
            }
        }

        return $configs;
    }

    public function getAllRepeaterConfigs(): array
    {
        $configs = [];

        foreach ($this->providers as $provider) {
            $config = $provider->getRepeaterConfig();
            if (! empty($config)) {
                $configs[$provider->slug()] = $config;
            }
        }

        return $configs;
    }

    public function getAllGalleryConfigs(): array
    {
        $configs = [];

        foreach ($this->providers as $provider) {
            $config = $provider->getGalleryConfig();
            if (! empty($config)) {
                $configs[$provider->slug()] = $config;
            }
        }

        return $configs;
    }

    public function getAllProfileFieldConfigs(): array
    {
        $configs = [];

        foreach ($this->providers as $provider) {
            $config = $provider->getProfileFieldsConfig();
            if (! empty($config)) {
                $configs[$provider->slug()] = $config;
            }
        }

        return $configs;
    }

    public function getAllNotificationTypes(): array
    {
        $types = [];

        foreach ($this->providers as $provider) {
            $moduleTypes = $provider->getNotificationTypes();
            foreach ($moduleTypes as $key => $config) {
                $types[$key] = array_merge([
                    'group' => $provider->name(),
                    'default_channels' => ['in_app'],
                    'default_enabled' => true,
                ], $config);
            }
        }

        return $types;
    }

    public function getAllEmailTemplates(): array
    {
        $templates = [];

        foreach ($this->providers as $provider) {
            $moduleTemplates = $provider->getEmailTemplates();
            if (! empty($moduleTemplates)) {
                $templates = array_merge($templates, $moduleTemplates);
            }
        }

        return $templates;
    }

    public function getAllDashboardData(): array
    {
        $data = [];

        foreach ($this->providers as $provider) {
            $providerData = $provider->getDashboardData();
            if (! empty($providerData)) {
                $data[$provider->slug()] = $providerData;
            }
        }

        return $data;
    }

    public function permissions(): array
    {
        $permissions = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->permissions() as $perm) {
                $permissions[] = $perm;
            }
        }

        return $permissions;
    }
}
