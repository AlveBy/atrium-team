<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

return [
    'modules' => [
        'members' => 'Equipe',
        'departments' => 'Departamentos',
    ],
    'create' => [
        'member' => 'Adicionar pessoa',
        'department' => 'Adicionar departamento',
    ],
    'fields' => [
        'name' => 'Nome',
        'position' => 'Cargo',
        'department' => 'Departamento',
        'photo' => 'Foto',
        'summary' => 'Descrição curta',
        'biography' => 'Biografia',
        'email' => 'E-mail',
        'phone' => 'Telefone',
    ],
];
