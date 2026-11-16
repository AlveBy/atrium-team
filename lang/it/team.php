<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

return [
    'modules' => [
        'members' => 'Team',
        'departments' => 'Reparti',
    ],
    'create' => [
        'member' => 'Aggiungi una persona',
        'department' => 'Aggiungi un reparto',
    ],
    'fields' => [
        'name' => 'Nome',
        'position' => 'Ruolo',
        'department' => 'Reparto',
        'photo' => 'Foto',
        'summary' => 'Descrizione breve',
        'biography' => 'Biografia',
        'email' => 'E-mail',
        'phone' => 'Telefono',
    ],
];
