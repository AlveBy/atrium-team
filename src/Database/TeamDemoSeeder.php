<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Team\Database;

use Atrium\Admin\Modules\DemoSeeder;
use Atrium\Team\Models\Department;
use Atrium\Team\Models\Member;

/**
 * Three people of the demo shop in two departments, in English, German and
 * Russian.
 */
final class TeamDemoSeeder extends DemoSeeder
{
    public function run(): bool
    {
        if (Member::query()->withoutGlobalScopes()->exists()) {
            return false;
        }

        $this->copyFiles(dirname(__DIR__, 2).'/resources/demo');

        $stores = Department::query()->create(['sort' => 100, 'name' => ['en' => 'Stores', 'de' => 'Filialen', 'ru' => 'Магазины']]);
        $service = Department::query()->create(['sort' => 200, 'name' => ['en' => 'Service', 'de' => 'Service', 'ru' => 'Сервис']]);

        $people = [
            [$stores, 'team-anna.svg', 'anna@example.com', [
                'en' => ['Anna Keller', 'anna-keller', 'Store manager', 'Runs the store on Market Street since it opened.'],
                'de' => ['Anna Keller', 'anna-keller', 'Filialleiterin', 'Leitet die Filiale in der Market Street seit der Eröffnung.'],
                'ru' => ['Анна Келлер', 'anna-keller', 'Управляющая магазином', 'Руководит магазином на Market Street с самого открытия.'],
            ]],
            [$stores, 'team-marek.svg', null, [
                'en' => ['Marek Nowak', 'marek-nowak', 'Sales consultant', 'Helps you choose a laptop that fits the work, not the advert.'],
                'de' => ['Marek Nowak', 'marek-nowak', 'Verkaufsberater', 'Hilft Ihnen, den Laptop zu finden, der zur Arbeit passt, nicht zur Werbung.'],
                'ru' => ['Марек Новак', 'marek-nowak', 'Консультант', 'Поможет выбрать ноутбук под задачи, а не под рекламу.'],
            ]],
            [$service, 'team-lea.svg', 'service@example.com', [
                'en' => ['Lea Brandt', 'lea-brandt', 'Repair technician', 'Replaces screens and batteries while you wait.'],
                'de' => ['Lea Brandt', 'lea-brandt', 'Servicetechnikerin', 'Tauscht Displays und Akkus, während Sie warten.'],
                'ru' => ['Лея Брандт', 'lea-brandt', 'Мастер по ремонту', 'Меняет экраны и аккумуляторы, пока вы ждёте.'],
            ]],
        ];

        foreach ($people as $sort => [$department, $photo, $email, $texts]) {
            $member = new Member(['department_id' => $department->id, 'sort' => ($sort + 1) * 100, 'preview_picture' => $this->path($photo), 'email' => $email]);

            foreach ($texts as $language => [$name, $code, $position, $summary]) {
                $member->setTranslation('name', $language, $name)
                    ->setTranslation('code', $language, $code)
                    ->setTranslation('position', $language, $position)
                    ->setTranslation('preview_text', $language, '<p>'.$summary.'</p>');
            }

            $member->save();
        }

        return true;
    }
}
