<header id="header" data-plugin-options="{'stickyEnabled': true, 'stickyEnableOnBoxed': true, 'stickyEnableOnMobile': false, 'stickyStartAt': 91, 'stickySetTop': '-91px', 'stickyChangeLogo': false}">
<div class="header-body border-0 box-shadow-none">
<div class="border-bottom-light">
<div class="header-container container container-xl-custom">
<div class="header-row py-4">
<div class="header-column"><a class="ngo-brand" href="{{ route('home') }}" aria-label="{{ __('public.ecosync_home') }}"><span class="ngo-logo-placeholder" aria-hidden="true">{{ __('public.e') }}</span><span><strong>{{ __('public.ecosync') }}</strong><small>{{ __('public.zelena_diploma') }}</small><span class="ngo-logo-note">{{ __('public.temporary_mark_awaiting_the_official_logo') }}</span></span></a></div>
<div class="header-column justify-content-end ngo-header-right">
<div class="ngo-header-note"><span>{{ __('public.the_future_is_planted_today_2') }}</span><small>{{ __('public.ecosync_zelena_diploma') }}</small></div><div class="d-none d-lg-flex ngo-header-language">@include('partials.language-switch')</div></div></div></div></div>
<div class="header-nav-bar z-index-0">
						<div class="container container-xl-custom">
							<div class="header-row py-2">
								<div class="header-column">
									<div class="header-row align-items-center justify-content-end">
										<div class="header-nav header-nav-links justify-content-start pb-1">
											<div class="header-nav-main header-nav-main-text-capitalize header-nav-main-effect-2 header-nav-main-sub-effect-1">
												<nav class="collapse" id="ngo-nav" aria-label="{{ __('public.main_navigation') }}">
<ul class="nav nav-pills" id="mainNav">
<li class="d-lg-none">@include('partials.language-switch')</li>
    @foreach (['home' => __('public.home'), 'mission' => __('public.our_mission_2'), 'join' => __('public.get_involved_2'), 'green-trace' => __('public.our_green_footprint'), 'gallery' => __('public.gallery'), 'contact' => __('public.contact')] as $name => $label)
        <li><a @class(['nav-link', 'active' => request()->routeIs($name)]) @if (request()->routeIs($name)) aria-current="page" @endif href="{{ route($name) }}">{{ App\Support\PublicText::label($label) }}</a></li>
    @endforeach
</ul></nav>
											</div>
										</div>
										<a href="{{ route('green-trace') }}#mapa" class="btn btn-modern btn-primary btn-arrow-effect-1 ngo-btn">{{ __('public.adopt_a_tree') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
										<button class="btn header-btn-collapse-nav" aria-label="{{ __('public.open_navigation') }}" aria-controls="ngo-nav" aria-expanded="false" data-bs-toggle="collapse" data-bs-target=".header-nav-main nav">
											<i class="fas fa-bars"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</header>
