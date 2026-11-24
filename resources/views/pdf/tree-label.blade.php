@extends('pdf.base')
@section('title', __('certificates.label'))
@section('document')
<div class="sheet label"><div class="brand"><img src="{{ $brandImage }}" alt=""><div class="brand-name">EcoSync</div><div class="initiative">Zelena diploma</div></div>
<div class="tree-id">{{ $identifier }}</div><div class="species">{{ $species }} @if($scientific)<br><em>{{ $scientific }}</em>@endif</div>
<h1>{{ __('certificates.scan') }}</h1><img class="qr" src="{{ $qrImage }}" alt="QR">
<p class="url"><a href="{{ $profileUrl }}">{{ $profileUrl }}</a></p><p class="small">{{ __('certificates.qr_help') }}</p></div>
@endsection
