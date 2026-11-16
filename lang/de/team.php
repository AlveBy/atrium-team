<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

return [
    'modules' => [
        'members' => 'Team',
        'departments' => 'Abteilungen',
    ],
    'create' => [
        'member' => 'Person hinzufügen',
        'department' => 'Abteilung hinzufügen',
    ],
    'fields' => [
        'name' => 'Name',
        'position' => 'Position',
        'department' => 'Abteilung',
        'photo' => 'Foto',
        'summary' => 'Kurzbeschreibung',
        'biography' => 'Biografie',
        'email' => 'E-Mail',
        'phone' => 'Telefon',
    ],
];
