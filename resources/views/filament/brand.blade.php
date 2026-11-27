@php($brand = app(\App\Content\SiteContent::class)->site())
<div class="panel-brand">
    @include('partials.brand-mark')
    <span><strong>{{ $brand['name'] }}</strong><small>{{ $brand['tagline'] }}</small></span>
</div>
