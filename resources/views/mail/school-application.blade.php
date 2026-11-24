<h1>Nova prijava škole</h1>
<p>{{ __('public.school_2') }} {{ $application->school_name }}</p>
<p>Kontakt osoba: {{ $application->contact_person }}</p>
<p>{{ __('public.email_3') }}{{ $application->email }}</p>
@if ($application->phone)<p>{{ __('public.phone') }}{{ $application->phone }}</p>@endif
<p>Grad: {{ $application->city }}</p>
<p>Model: {{ (App\Support\DomainRules::PLANTING + ['undecided' => 'Dogovor sa EcoSync'])[$application->desired_planting_model] }}</p>
@if ($application->message)<p>{{ $application->message }}</p>@endif
<a href="{{ App\Filament\Resources\SchoolApplicationResource::getUrl('edit', ['record'=>$application], panel:'admin') }}">Otvori zahtev u administraciji</a>
