@if ($trees->isNotEmpty())
<h2>{{ __('public.trees') }}</h2><div class="row g-4">@foreach ($trees as $tree)<div class="col-md-4"><article class="ngo-participation"><h3><a href="{{ route('trees.show',$tree->public_identifier) }}">{{ $tree->public_identifier }}</a></h3><p>{{ $tree->species->localized('common_name') }}</p></article></div>@endforeach</div>
@endif
@if (isset($groups) && $groups->isNotEmpty())
<h2>{{ __('public.tree_groups') }}</h2><div class="row g-4">@foreach ($groups as $group)<div class="col-md-4"><article class="ngo-participation"><h3><a href="{{ route('groups.show',$group->slug) }}">{{ $group->localized('name') }}</a></h3><p>{{ __('public.declared_total') }} {{ $group->declared_tree_count ?? __('public.not_confirmed_2') }}<br>{{ __('public.individually_registered') }} {{ $group->trees_count }}</p></article></div>@endforeach</div>
@endif
@if (isset($albums) && $albums->isNotEmpty())<h2>{{ __('public.photos') }}</h2><div class="row g-4">@foreach ($albums as $album)<x-album-card :album="$album" />@endforeach</div>@endif
