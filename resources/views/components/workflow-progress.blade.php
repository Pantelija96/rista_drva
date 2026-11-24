@props(['kind' => 'adoption'])
<ol class="ngo-progress" aria-label="{{ $kind === 'school' ? __('public.next_steps') : __('public.adoption') }}">
@foreach(__('flows.'.$kind) as $step)<li>{{ $step }}</li>@endforeach
</ol>
