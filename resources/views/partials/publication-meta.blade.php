@php
$published = \App\Content\PublicationContent::date($publication['date'] ?? null);
$category = is_string($publication['category'] ?? null) ? trim($publication['category']) : '';
@endphp
@if ($published || $category !== '')
  <p class="publication-meta">
    @if ($published)
      <time datetime="{{ $published->format('Y-m-d') }}">{{ $published->format('d.m.Y') }}</time>
    @endif
    @if ($category !== '')
      <span>{{ $category }}</span>
    @endif
  </p>
@endif
