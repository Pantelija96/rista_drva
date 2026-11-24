<h1>{{ $heading }}</h1>
@foreach ($paragraphs as $paragraph)<p>{{ $paragraph }}</p>@endforeach
@if ($url)<p><a href="{{ $url }}">{{ __('public.open_secure_page') }}</a></p>@endif
<p>{{ __('public.ecosync_zelena_diploma_2') }}<br>{{ __('public.the_future_is_planted_today_2') }}</p>
