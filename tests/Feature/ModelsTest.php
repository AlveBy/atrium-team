<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Team\Tests\Feature;

use Atrium\Admin\Fields\Field;
use Atrium\Team\Database\TeamDemoSeeder;
use Atrium\Team\Models\Department;
use Atrium\Team\Models\Member;
use Atrium\Team\TeamServiceProvider;
use Atrium\Team\Tests\TestCase;
use Atrium\Theme\Pages;
use Atrium\Theme\RecordView;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Departments and people: how they hang together, how the team page
 * groups them, and what the panel shows of them.
 */
final class ModelsTest extends TestCase
{
    public function test_a_department_has_its_people_and_a_person_their_department(): void
    {
        $partners = $this->department('Partners');
        $ines = $this->member('Ines', 'ines', ['department_id' => $partners->id]);
        $this->member('Tom', 'tom');

        $this->assertSame([$ines->id], $partners->members()->pluck('id')->all());
        $this->assertTrue($ines->department?->is($partners));
        $this->assertSame($partners->id, (new Member(['department_id' => (string) $partners->id]))->department_id);
    }

    public function test_the_flags_and_the_order_are_read_back_as_such(): void
    {
        $department = Department::query()->create(['name' => ['en' => 'A'], 'active' => 0, 'sort' => '7']);

        $this->assertFalse($department->fresh()?->active);
        $this->assertSame(7, $department->fresh()?->sort);
        $this->assertTrue($this->member('Ines', 'ines', ['active' => 1])->fresh()?->active);
    }

    public function test_the_page_groups_people_by_active_department_in_order(): void
    {
        // The same order: the older department first.
        $second = $this->department('Older', 100);
        $first = $this->department('Newer', 100);
        $closed = Department::query()->create(['name' => ['en' => 'Closed'], 'sort' => 1, 'active' => false]);
        $early = $this->department('Early', 50);

        $anna = $this->member('Anna', 'anna', ['department_id' => $second->id]);
        $bob = $this->member('Bob', 'bob', ['department_id' => $first->id]);
        $carl = $this->member('Carl', 'carl', ['department_id' => $closed->id]);
        $dora = $this->member('Dora', 'dora');
        $eve = $this->member('Eve', 'eve', ['department_id' => $early->id]);
        $fay = $this->member('Fay', 'fay', ['department_id' => $second->id]);

        // Bob of the newer department comes first: the order is still the departments'.
        $groups = $this->groupsOf([$bob, $anna, $carl, $dora, $eve, $fay, $early, 'not a record']);

        $this->assertSame(
            [['Early', ['Eve']], ['Older', ['Anna', 'Fay']], ['Newer', ['Bob']], [null, ['Carl', 'Dora']]],
            array_map(fn (array $group): array => [$group['title'], array_map(fn (RecordView $person): string => (string) $person->record->name, $group['people'])], $groups),
        );
        $this->assertSame([0, 1], array_keys($groups[1]['people']));
        $this->assertSame([0, 1], array_keys($groups[3]['people']));
    }

    public function test_the_demo_fills_departments_and_people_with_their_pictures(): void
    {
        Storage::fake('public');

        $this->assertTrue((new TeamDemoSeeder)->run());

        $departments = Department::query()->orderBy('id')->get();
        $this->assertSame([[100, 'Stores', 'Filialen'], [200, 'Service', 'Service']], $departments->map(fn (Department $department): array => [
            $department->sort, $department->getTranslation('name', 'en'), $department->getTranslation('name', 'de'),
        ])->all());

        $people = Member::query()->orderBy('id')->get();
        $this->assertSame([
            ['Anna Keller', 'anna-keller', 'Store manager', '<p>Runs the store on Market Street since it opened.</p>', 'Анна Келлер', $departments[0]->id, 100, 'demo/team-anna.svg', 'anna@example.com'],
            ['Marek Nowak', 'marek-nowak', 'Sales consultant', '<p>Helps you choose a laptop that fits the work, not the advert.</p>', 'Марек Новак', $departments[0]->id, 200, 'demo/team-marek.svg', null],
            ['Lea Brandt', 'lea-brandt', 'Repair technician', '<p>Replaces screens and batteries while you wait.</p>', 'Лея Брандт', $departments[1]->id, 300, 'demo/team-lea.svg', 'service@example.com'],
        ], $people->map(fn (Member $member): array => [
            $member->getTranslation('name', 'en'),
            $member->getTranslation('code', 'en'),
            $member->getTranslation('position', 'en'),
            $member->getTranslation('preview_text', 'en'),
            $member->getTranslation('name', 'ru'),
            $member->department_id,
            $member->sort,
            $member->preview_picture,
            $member->email,
        ])->all());

        foreach (['team-anna.svg', 'team-lea.svg', 'team-marek.svg'] as $file) {
            Storage::disk('public')->assertExists('demo/'.$file);
            $this->assertSame(file_get_contents(__DIR__.'/../../resources/demo/'.$file), Storage::disk('public')->get('demo/'.$file));
        }
    }

    public function test_the_demo_adds_nothing_to_a_team_that_has_people(): void
    {
        Storage::fake('public');
        $this->member('Ines', 'ines');

        $this->assertFalse((new TeamDemoSeeder)->run());

        $this->assertSame(1, Member::query()->count());
        $this->assertSame(0, Department::query()->count());
        Storage::disk('public')->assertMissing('demo/team-anna.svg');
    }

    public function test_a_page_of_people_all_in_departments_has_no_group_without_a_title(): void
    {
        $partners = $this->department('Partners');
        $ines = $this->member('Ines', 'ines', ['department_id' => $partners->id]);

        $groups = $this->groupsOf([$ines]);

        $this->assertCount(1, $groups);
        $this->assertSame('Partners', $groups[0]['title']);
    }

    public function test_a_page_without_records_has_no_groups(): void
    {
        $this->member('Ines', 'ines');

        $view = View::make('atrium-team::index', ['records' => null, 'title' => 'Team']);
        View::callComposer($view);

        $this->assertSame([], $view->getData()['groups']);
    }

    public function test_the_forms_and_lists_of_the_panel(): void
    {
        $departments = $this->byName(Department::getAdministrationFields());
        $this->assertSame(['active', 'name', 'sort'], array_keys($departments));
        $this->assertTrue($departments['active']->getDefault());
        $this->assertSame(500, $departments['sort']->getDefault());
        $this->assertSame(['id', 'name', 'sort', 'active'], $this->names(Department::getAdministrationListColumns()));

        $members = $this->byName(Member::getAdministrationFields());
        $this->assertSame(['active', 'name', 'code', 'position', 'department_id', 'sort', 'preview_picture', 'preview_text', 'detail_text', 'email', 'phone'], array_keys($members));
        $this->assertTrue($members['active']->getDefault());
        $this->assertSame(500, $members['sort']->getDefault());
        $this->assertSame(['id', 'preview_picture', 'name', 'position', 'department.name', 'sort', 'active'], $this->names(Member::getAdministrationListColumns()));
    }

    public function test_the_theme_draws_the_list_with_the_team_view(): void
    {
        $this->assertSame('atrium-team::index', Member::themeView('index'));
        $this->assertNull(Member::themeView('item'));
    }

    public function test_the_views_and_translations_can_be_published(): void
    {
        $paths = [
            'atrium-team-views' => [__DIR__.'/../../resources/views', resource_path('views/vendor/atrium-team')],
            'atrium-team-lang' => [__DIR__.'/../../lang', app()->langPath('vendor/atrium-team')],
        ];

        foreach ($paths as $tag => [$from, $to]) {
            $published = ServiceProvider::pathsToPublish(TeamServiceProvider::class, $tag);

            $this->assertCount(1, $published, $tag);
            $this->assertSame(realpath($from), realpath((string) array_key_first($published)), $tag);
            $this->assertSame($to, $published[array_key_first($published)], $tag);
        }
    }

    /**
     * @param  array<int,mixed>  $items
     * @return array<int,array{title:?string,people:array<int,RecordView>}>
     */
    private function groupsOf(array $items): array
    {
        $pages = app(Pages::class);
        $records = array_map(fn ($item) => $item instanceof Member || $item instanceof Department ? new RecordView($item, $pages) : $item, $items);
        $view = View::make('atrium-team::index', ['records' => new LengthAwarePaginator($records, count($records), 20), 'title' => 'Team']);
        View::callComposer($view);

        return $view->getData()['groups'];
    }

    /**
     * @param  array<int,Field>  $fields
     * @return array<string,Field>
     */
    private function byName(array $fields): array
    {
        return array_combine($this->names($fields), $fields);
    }

    /**
     * @param  array<int,mixed>  $items
     * @return array<int,string>
     */
    private function names(array $items): array
    {
        return array_map(fn ($item): string => $item->getName(), $items);
    }
}
