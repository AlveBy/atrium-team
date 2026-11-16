{{--
    This file is part of Atrium (https://github.com/AlveBy/atrium).
    Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
--}}
{{-- The team: a photo, a name and a position for each person, by department. --}}
@extends('atrium-theme::layout', ['metaTitle' => $title])

@section('content')
    <h1>{{ $title }}</h1>

    @forelse ($groups as $group)
        <section class="team-group" @if ($group['title']) aria-labelledby="team-group-{{ $loop->index }}" @endif>
            @if ($group['title'])
                <h2 id="team-group-{{ $loop->index }}">{{ $group['title'] }}</h2>
            @endif
            <ul class="cards team-cards">
                @foreach ($group['people'] as $person)
                    @continue(! ($url = $person->url()))
                    <li class="card team-card">
                        @if ($photo = $person->thumbnail())
                            <img src="{{ $photo }}" alt="" width="320" height="320" loading="lazy">
                        @endif
                        <div class="card-body">
                            <h3><a href="{{ $url }}">{{ $person->title() }}</a></h3>
                            @if (filled($position = $person->record->getAttribute('position')))
                                <p class="card-text">{{ $position }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="empty">{{ __('atrium-theme::theme.empty') }}</p>
    @endforelse

    @if ($records !== null)
        @include('atrium-theme::partials.pagination', ['paginator' => $records])
    @endif

    @push('head')
    <style>
        .team-group + .team-group { margin-top: 2rem; }
        .team-cards { grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr)); }
        .team-card img { aspect-ratio: 1; }
        .team-card .card-text { margin: 0; }
    </style>
    @endpush
@endsection
