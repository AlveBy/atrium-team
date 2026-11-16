<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Team\Tests;

use Atrium\Admin\AtriumServiceProvider;
use Atrium\Admin\Models\AdminUser;
use Atrium\Admin\Support\PublicUrl;
use Atrium\Team\Models\Department;
use Atrium\Team\Models\Member;
use Atrium\Team\TeamServiceProvider;
use Atrium\Theme\ThemeServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tighten\Ziggy\ZiggyServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            InertiaServiceProvider::class,
            ZiggyServiceProvider::class,
            AtriumServiceProvider::class,
            ThemeServiceProvider::class,
            TeamServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $config = $app['config'];
        $config->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $config->set('app.name', 'Harbour Law');
        // CI runs the suite against MySQL and PostgreSQL too: the texts are
        // JSON columns, read differently by each.
        $target = env('DB_CONNECTION');
        $config->set('database.default', in_array($target, ['mysql', 'pgsql'], true) ? $target : 'testing');
        $config->set('atrium.demo', false);
        $config->set('atrium.modules', ['alveby/atrium-team']);
        $config->set('atrium.auth.two_factor.enabled', false);
        $config->set('atrium.dashboard.timings.enabled', false);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        PublicUrl::forgetPaths();
    }

    /**
     * On MySQL and PostgreSQL the whole suite shares one database, set up by
     * whichever package's tests run first: the team tables come with these.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function admin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret-password',
            'is_active' => true,
        ]);

        $admin->forceFill(['is_superadmin' => true])->save();

        return $admin;
    }

    /**
     * @param  array<string,mixed>  $attributes
     */
    protected function member(string $name, string $code, array $attributes = []): Member
    {
        return Member::query()->create(['name' => ['en' => $name], 'code' => ['en' => $code], ...$attributes]);
    }

    protected function department(string $name, int $sort = 500): Department
    {
        return Department::query()->create(['name' => ['en' => $name], 'sort' => $sort]);
    }

    protected function root(): string
    {
        return rtrim(url('/'), '/');
    }
}
