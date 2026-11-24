@if($adoption)
<p>{{ __('certificates.reference') }}: {{ app(App\Services\AdoptionCertificates::class)->reference($adoption) }}</p>
@if(app(App\Services\AdoptionCertificates::class)->eligible($adoption))
<p>{{ __('certificates.available') }}</p>
@foreach(['sr','en'] as $locale)<p><a href="{{ route('certificates.download', ['locale'=>$locale,'adoption'=>$adoption->public_id]) }}">{{ __('certificates.'.$locale) }}</a></p>@endforeach
@else<p>{{ __('certificates.unavailable') }}</p>@endif
@endif
