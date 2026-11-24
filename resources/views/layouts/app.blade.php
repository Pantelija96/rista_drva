<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'sr-Latn' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Security-Policy" content="form-action 'self'">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <title>@yield('title')</title>
    <meta name="keywords" content="EcoSync, Zelena diploma, sadnja stabala, usvoji drvo">
    <meta name="description" content="@yield('description')">
    <meta property="og:title" content="@yield('title')">
    <meta property="og:description" content="@yield('description')">
    @if(request()->route() && !request()->is('admin*'))
    <link rel="canonical" href="{{ App\Support\LocaleUrls::for(app()->getLocale(), false) }}">
    @foreach (['sr','en'] as $language)<link rel="alternate" hreflang="{{ $language }}" href="{{ App\Support\LocaleUrls::for($language, false) }}">@endforeach
    <link rel="alternate" hreflang="x-default" href="{{ App\Support\LocaleUrls::for('sr', false) }}">
    @endif
    @if(View::hasSection('noindex') || request()->is('*staratelj*','*potvrda*','*usvajanje/primljeno*'))<meta name="robots" content="noindex,nofollow">@endif
    <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_GB' : 'sr_RS' }}">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/favicon.svg') }}">

    {{-- Porto vendor, theme and skin styles --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/animate/animate.compat.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/owl.carousel/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/owl.carousel/assets/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/magnific-popup/magnific-popup.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme-elements.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demos/demo-renewable-energy.css') }}">
    <link id="skinCSS" rel="stylesheet" href="{{ asset('assets/css/skins/skin-renewable-energy.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/porto-palette.css') }}">
    @vite(['resources/css/app.css', 'resources/css/maps.css'])
    @stack('styles')
</head>
<body data-draft-warning="{{ __('flows.draft') }}" class="alternative-font-7 ngo-site">
    <a class="ngo-skip" href="#main">{{ __('public.skip_to_content') }}</a>
    <div class="body">
        @include('partials.header')
        <main id="main" class="main">
            @yield('content')
        </main>
        @include('partials.footer')
    </div>
    @include('partials.demo-modals')

    <script src="{{ asset('assets/vendor/plugins/js/plugins.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    @vite(['resources/js/app.js', 'resources/js/maps.js'])
    <script defer src="{{ asset('assets/js/theme.init.js') }}"></script>
    @stack('scripts')
</body>
</html>
