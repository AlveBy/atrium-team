<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

return [
    'modules' => [
        'members' => 'Team',
        'departments' => 'Departments',
    ],
    'create' => [
        'member' => 'Add a person',
        'department' => 'Add a department',
    ],
    'fields' => [
        'name' => 'Name',
        'position' => 'Position',
        'department' => 'Department',
        'photo' => 'Photo',
        'summary' => 'Short description',
        'biography' => 'Biography',
        'email' => 'Email',
        'phone' => 'Phone',
    ],
];
