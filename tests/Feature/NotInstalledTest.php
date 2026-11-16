<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Team\Tests\Feature;

use Atrium\Team\Tests\TestCase;

/**
 * Required with Composer but not installed: the team is neither in the panel
 * nor on the site.
 */
final class NotInstalledTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('atrium.modules', []);
    }

    public function test_the_module_stays_out_of_the_panel_and_the_site(): void
    {
        $this->actingAs($this->admin(), 'admin')->get('/admin/team_members')->assertNotFound();
        $this->get('/team')->assertNotFound();
    }
}
