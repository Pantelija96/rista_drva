@extends('pdf.base')
@section('title', __('certificates.heading'))
@section('document')
<div class="sheet {{ mb_strlen($guardian.$species.$scientific.$school)>230 ? 'compact' : '' }}">
<div class="brand"><img src="{{ $brandImage }}" alt=""><div class="brand-name">EcoSync</div><div class="initiative">Zelena diploma</div></div>
<h1>{{ __('certificates.heading') }}</h1>
<p class="statement">{{ __('certificates.statement') }}</p>
<div class="tree-id">{{ $identifier }}</div><div class="species">{{ $species }} @if($scientific)<br><em class="small">{{ $scientific }}</em>@endif</div>
<table class="details">
<tr><td class="caption">{{ __('certificates.guardian') }}</td><td>{{ $guardian }}</td></tr>
<tr><td class="caption">{{ __('certificates.adopted') }}</td><td>{{ $adopted }}</td></tr>
<tr><td class="caption">{{ __('certificates.type') }}</td><td>{{ $adoptionType }}</td></tr>
@if($school)<tr><td class="caption">{{ __('certificates.school') }}</td><td>{{ $school }}</td></tr>@endif
@if($generation)<tr><td class="caption">{{ __('certificates.generation') }}</td><td>{{ $generation }}</td></tr>@endif
</table>
<table class="qr-block"><colgroup><col style="width:30%"><col style="width:70%"></colgroup><tr><td><img class="qr" src="{{ $qrImage }}" alt="QR"></td><td><strong>{{ __('certificates.profile') }}</strong><p class="url"><a href="{{ $profileUrl }}">{{ $profileUrl }}</a></p><p class="small">{{ __('certificates.current_status') }}</p></td></tr></table>
<p class="small">{{ __('certificates.disclaimer') }}</p>
<div class="reference">{{ __('certificates.reference') }}<br><strong>{{ $reference }}</strong><br>{{ __('certificates.generated') }}: {{ $generated }}</div>
</div>
@endsection
