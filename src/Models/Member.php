<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Team\Models;

use Atrium\Admin\Columns\BooleanColumn;
use Atrium\Admin\Columns\ImageColumn;
use Atrium\Admin\Columns\TextColumn;
use Atrium\Admin\Contracts\Administrable;
use Atrium\Admin\Fields\BelongsToField;
use Atrium\Admin\Fields\CheckboxField;
use Atrium\Admin\Fields\ContentField;
use Atrium\Admin\Fields\ImageField;
use Atrium\Admin\Fields\NumberField;
use Atrium\Admin\Fields\SlugField;
use Atrium\Admin\Fields\TextField;
use Atrium\Admin\Traits\BelongsToSite;
use Atrium\Admin\Traits\InteractsWithAdmin;
use Atrium\Admin\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One person of the team: a lawyer, a doctor, a teacher. The name, the
 * position and the texts are written per language; the photo, the email and
 * the phone are the same in all of them.
 *
 * @property int $id
 * @property int|null $site_id
 * @property int|null $department_id
 * @property bool $active
 * @property int $sort
 * @property string|null $preview_picture
 * @property string|null $email
 * @property string|null $phone
 */
class Member extends Model implements Administrable
{
    use BelongsToSite, InteractsWithAdmin, Translatable;

    protected $table = 'team_members';

    /** @var array<int,string> */
    protected array $translatable = ['name', 'code', 'position', 'preview_text', 'detail_text'];

    protected $fillable = ['site_id', 'department_id', 'name', 'code', 'position', 'active', 'sort', 'preview_picture', 'preview_text', 'detail_text', 'email', 'phone'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'sort' => 'integer', 'department_id' => 'integer'];
    }

    /**
     * @return BelongsTo<Department,$this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public static function getAdminLabel(): string
    {
        return __('atrium-team::team.modules.members');
    }

    public static function getAdminCreateLabel(): string
    {
        return __('atrium-team::team.create.member');
    }

    public static function getAdminRoutePrefix(): string
    {
        return 'team_members';
    }

    public static function getDefaultSortColumn(): string
    {
        return 'sort';
    }

    public static function getDefaultSortDirection(): string
    {
        return 'asc';
    }

    /**
     * The team page shows faces, names and positions, by department.
     */
    public static function themeView(string $page): ?string
    {
        return $page === 'index' ? 'atrium-team::index' : null;
    }

    public static function getAdministrationFields(): array
    {
        return [
            CheckboxField::make('active')->label(__('atrium::fields.standard.active'))->default(true),
            TextField::make('name')->label(__('atrium-team::team.fields.name'))->required(),
            SlugField::make('code')->label(__('atrium::fields.standard.code'))->from('name')->unique('team_members', 'code'),
            TextField::make('position')->label(__('atrium-team::team.fields.position')),
            BelongsToField::make('department_id')->label(__('atrium-team::team.fields.department'))->model(Department::class),
            NumberField::make('sort')->label(__('atrium::fields.standard.sort'))->default(500),
            ImageField::make('preview_picture')->label(__('atrium-team::team.fields.photo'))->directory('team'),
            ContentField::make('preview_text')->label(__('atrium-team::team.fields.summary')),
            ContentField::make('detail_text')->label(__('atrium-team::team.fields.biography')),
            TextField::make('email')->label(__('atrium-team::team.fields.email'))->email(),
            TextField::make('phone')->label(__('atrium-team::team.fields.phone'))->tel(),
        ];
    }

    public static function getAdministrationListColumns(): array
    {
        return [
            TextColumn::make('id')->label(__('atrium::fields.standard.id'))->sortable(),
            ImageColumn::make('preview_picture')->label(__('atrium-team::team.fields.photo')),
            TextColumn::make('name')->label(__('atrium-team::team.fields.name'))->sortable()->searchable(),
            TextColumn::make('position')->label(__('atrium-team::team.fields.position')),
            TextColumn::make('department.name')->label(__('atrium-team::team.fields.department')),
            TextColumn::make('sort')->label(__('atrium::fields.standard.sort'))->sortable(),
            BooleanColumn::make('active')->label(__('atrium::fields.standard.active')),
        ];
    }
}
