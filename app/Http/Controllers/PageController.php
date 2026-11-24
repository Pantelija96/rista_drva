<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use App\Models\School;
use App\Models\SiteStatistic;
use App\Services\PublicCommunity;
use App\Support\DemoContent;
use Illuminate\Contracts\View\View;

final class PageController
{
    public function home(): View
    {
        return view('pages.home', [
            'statistics' => SiteStatistic::where('visible', true)->orderBy('sort_order')->get()->map(fn ($s) => ['key' => $s->key, 'count' => $s->value, 'display' => number_format($s->value, 0, ',', '.').$s->suffix, 'label' => $s->localized('label')]),
            'events' => DemoContent::events(),
            'albums' => GalleryAlbum::published()->with(['images' => fn ($q) => $q->where('published', true)->orderBy('sort_order')])->orderBy('sort_order')->limit(3)->get(),
        ]);
    }

    public function mission(): View
    {
        return view('pages.nasa-misija');
    }

    public function join(): View
    {
        return view('pages.pridruzi-se');
    }

    public function greenTrace(): View
    {
        return view('pages.nas-zeleni-trag', ['schools' => School::where('status', 'published')->with(['generations' => fn ($q) => $q->published()->orderByDesc('graduation_year')])->orderBy('name')->get(['id', 'name', 'slug', 'city']), 'guardians' => app(PublicCommunity::class)->guardians()]);
    }

    public function gallery(): View
    {
        return view('pages.galerija', ['albums' => GalleryAlbum::published()->with(['images' => fn ($q) => $q->where('published', true)->orderBy('sort_order')])->orderBy('sort_order')->get()]);
    }

    public function contact(): View
    {
        return view('pages.kontakt');
    }
}
