<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Team\Tests\Feature;

use Atrium\Team\Models\Member;
use Atrium\Team\Tests\TestCase;
use Inertia\Testing\AssertableInertia;

/**
 * Editors keep the people and their departments in the admin.
 */
final class AdminTest extends TestCase
{
    public function test_the_installed_module_brings_both_lists(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/team_members')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('title', 'Team'));

        $this->get('/admin/team_departments')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('title', 'Departments'));
    }

    public function test_a_person_is_created_with_a_department_and_edited(): void
    {
        $partners = $this->department('Partners');

        $this->actingAs($this->admin(), 'admin')
            ->post('/admin/team_members', [
                'name' => ['en' => 'Ines Hartmann'],
                'code' => ['en' => 'ines-hartmann'],
                'position' => ['en' => 'Managing partner'],
                'department_id' => $partners->id,
                'email' => 'ines@example.com',
                'active' => true,
                'sort' => 10,
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.team_members.index'));

        $person = Member::query()->sole();
        $this->assertSame('Managing partner', $person->getTranslation('position', 'en'));
        $this->assertSame($partners->id, $person->department_id);
        $this->assertNotNull($person->site_id);

        $this->put("/admin/team_members/{$person->id}", ['name' => ['en' => 'Ines Hartmann'], 'code' => ['en' => 'ines-hartmann'], 'position' => ['en' => 'Senior partner'], 'email' => 'not an email', 'active' => true, 'sort' => 10])
            ->assertSessionHasErrors('email');

        $this->put("/admin/team_members/{$person->id}", ['name' => ['en' => 'Ines Hartmann'], 'code' => ['en' => 'ines-hartmann'], 'position' => ['en' => 'Senior partner'], 'active' => true, 'sort' => 10])
            ->assertSessionDoesntHaveErrors();
        $this->assertSame('Senior partner', $person->fresh()?->getTranslation('position', 'en'));
    }
}
