@if($tree && App\Models\Tree::published()->whereKey($tree->id)->exists())
<div class="space-y-3">
<p>QR kodovi vode samo na javni profil stabla. Za štampu koristite SVG ili PDF oznaku.</p>
<img src="{{ route('trees.qr', ['locale'=>'sr','identifier'=>$tree->public_identifier]) }}" alt="QR · {{ $tree->public_identifier }}" width="180" height="180">
@foreach(['sr','en'] as $locale)<p><a href="{{ route('trees.qr', ['locale'=>$locale,'identifier'=>$tree->public_identifier,'download'=>1]) }}">{{ __('certificates.qr_'.$locale) }}</a> · <a href="{{ route('trees.label', ['locale'=>$locale,'identifier'=>$tree->public_identifier]) }}">{{ __('certificates.label_'.$locale) }}</a></p>@endforeach
</div>
@else<p>QR kod i oznaka dostupni su nakon objavljivanja važećeg javnog profila stabla.</p>@endif
