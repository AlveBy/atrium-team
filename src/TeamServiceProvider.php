<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Team;

use Atrium\Team\Models\Member;
use Atrium\Theme\RecordView;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * The models, the /team page and the migrations come from extra.atrium in
 * composer.json, once `atrium:module:install alveby/atrium-team` has run.
 */
final class TeamServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'atrium-team');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'atrium-team');

        // The theme gives the team page its people; here they are put in
        // their departments, in the departments' order.
        View::composer('atrium-team::index', function (\Illuminate\View\View $view): void {
            $records = $view->getData()['records'] ?? null;
            $groups = [];

            if ($records instanceof Paginator) {
                $people = array_filter($records->items(), static fn ($item): bool => $item instanceof RecordView && $item->record instanceof Member);
                $departments = collect($people)->map(static fn (RecordView $person) => $person->record->getRelationValue('department'))
                    ->filter(static fn ($department): bool => $department !== null && $department->active)
                    ->unique('id')
                    ->sortBy([['sort', 'asc'], ['id', 'asc']]);

                foreach ($departments as $department) {
                    $groups[] = ['title' => (string) $department->name, 'people' => array_values(array_filter($people, static fn (RecordView $person): bool => $person->record->getAttribute('department_id') === $department->id))];
                }

                $rest = array_values(array_filter($people, static fn (RecordView $person): bool => ! $departments->contains('id', $person->record->getAttribute('department_id'))));

                if ($rest !== []) {
                    $groups[] = ['title' => null, 'people' => $rest];
                }
            }

            $view->with('groups', $groups);
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/atrium-team'),
            ], 'atrium-team-views');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/atrium-team'),
            ], 'atrium-team-lang');
        }
    }
}
