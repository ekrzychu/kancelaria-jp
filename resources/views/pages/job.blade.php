@extends('layouts.public')

@section('content')
@php
  [$applyUrl, $applyLabel] = $job->applicationAction();
  $facts = $job->facts();
@endphp
<section class="career-hero" aria-labelledby="job-title">
  <div class="career-frame career-split" data-reveal-children data-reveal-sequence>
    <div>
      <a class="section-kicker job-back" href="/kariera/#career-opportunities-title">Kariera · Aktualne oferty</a>
      <h1 id="job-title">{{ $job->title }}</h1>
    </div>
    <div class="job-intro">
      @if (filled($job->description))
        <p class="career-intro">{{ $job->description }}</p>
      @endif
      <a class="button" href="{{ $applyUrl }}">{{ $applyLabel }}</a>
    </div>
  </div>
</section>

<div class="career-frame job-layout">
  @if ($facts)
    <aside class="job-summary" aria-labelledby="job-facts-title" data-reveal>
      <h2 id="job-facts-title">Informacje o ofercie</h2>
      <dl class="job-facts">
        @foreach ($facts as $label => $value)
          <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
        @endforeach
      </dl>
    </aside>
  @endif

  <div class="job-reading">
    @if (filled($job->body))
      <section class="job-section rich-text" data-reveal>
        <h2>Opis stanowiska</h2>
        <p class="job-copy">{{ $job->body }}</p>
      </section>
    @endif
    @foreach (\App\Models\JobOffer::LISTS as $field => $label)
      @continue (empty($job->{$field}))
      <section class="job-section rich-text" data-reveal>
        <h2>{{ $label }}</h2>
        <ul>
          @foreach ($job->{$field} as $entry)
            <li>{{ $entry }}</li>
          @endforeach
        </ul>
      </section>
    @endforeach

    <section class="job-section rich-text" data-reveal>
      <h2>Aplikowanie</h2>
      @foreach (\App\Models\JobOffer::APPLICATION_TEXT as $field => $label)
        @continue (blank($job->{$field}))
        <h3>{{ $label }}</h3>
        <p class="job-copy">{{ $job->{$field} }}</p>
      @endforeach
      <div class="editorial-actions">
        <a class="button" href="{{ $applyUrl }}">{{ $applyLabel }}</a>
        <a class="text-action" href="/kariera/#career-opportunities-title">Wróć do ofert pracy</a>
      </div>
    </section>
  </div>
</div>
@endsection
