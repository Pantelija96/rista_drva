@if ($maintenance)
<div><h3>Fotografije za pregled</h3><div style="display:flex;flex-wrap:wrap;gap:16px">
@foreach ($maintenance->photos as $photo)
@if (Storage::disk('local')->exists($photo->path))<a href="{{ route('maintenance.photo',$photo->public_id) }}" target="_blank" rel="noopener"><img style="max-width:220px;max-height:180px" src="{{ route('maintenance.photo',$photo->public_id) }}" alt="{{ $photo->alt_text }}"></a>@endif
@endforeach</div><p>Za javnu objavu odobrite tekst i pojedinačne fotografije kroz akciju na listi izveštaja.</p></div>
@endif
