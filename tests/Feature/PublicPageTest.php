<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Team\Tests\Feature;

use Atrium\Team\Database\TeamDemoSeeder;
use Atrium\Team\Tests\TestCase;

/**
 * The team page lists the people by department; each person has a page.
 */
final class PublicPageTest extends TestCase
{
    public function test_the_team_page_groups_people_by_department_in_order(): void
    {
        $associates = $this->department('Associates', 200);
        $partners = $this->department('Partners', 100);
        $this->member('Tom Reyes', 'tom-reyes', ['department_id' => $associates->id, 'position' => ['en' => 'Associate']]);
        $this->member('Ines Hartmann', 'ines-hartmann', ['department_id' => $partners->id, 'position' => ['en' => 'Managing partner']]);
        $this->member('Office Cat', 'office-cat');
        $this->member('Gone Away', 'gone-away', ['active' => false]);

        $this->get('/team')
            ->assertOk()
            ->assertSeeInOrder(['<h1>Team</h1>', 'Partners', 'Ines Hartmann', 'Managing partner', 'Associates', 'Tom Reyes', 'Office Cat'], false)
            ->assertDontSee('Gone Away')
            ->assertSee('href="'.$this->root().'/team/ines-hartmann"', false);
    }

    public function test_a_person_has_a_page_with_position_and_contacts(): void
    {
        $this->member('Ines Hartmann', 'ines-hartmann', [
            'position' => ['en' => 'Managing partner'],
            'detail_text' => ['en' => '<p>Twenty years of shipping law.</p>'],
            'email' => 'ines@example.com',
        ]);

        $this->get('/team/ines-hartmann')
            ->assertOk()
            ->assertSee('<h1>Ines Hartmann</h1>', false)
            ->assertSee('Twenty years of shipping law.')
            ->assertSee('Managing partner')
            ->assertSee('ines@example.com');
    }

    public function test_the_demo_fills_the_team_once(): void
    {
        $this->assertTrue((new TeamDemoSeeder)->run());
        $this->assertFalse((new TeamDemoSeeder)->run());

        $this->get('/team')->assertOk()->assertSee('Anna Keller')->assertSee('Repair technician');
    }
}
