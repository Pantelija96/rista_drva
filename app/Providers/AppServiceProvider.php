<?php

namespace App\Providers;

use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Inquiry;
use App\Models\MailOutbox;
use App\Models\School;
use App\Models\SchoolApplication;
use App\Models\SchoolGeneration;
use App\Models\SiteStatistic;
use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Models\TreeGroup;
use App\Models\TreeMaintenanceRecord;
use App\Models\TreeSpecies;
use App\Models\User;
use App\Observers\MediaObserver;
use App\Policies\AdminPolicy;
use App\Policies\MaintenancePolicy;
use App\Policies\SchoolApplicationPolicy;
use App\Policies\UserPolicy;
use App\Policies\WorkflowPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \Illuminate\Support\Facades\URL::defaults(['locale' => 'sr']);
        foreach ([School::class, SchoolGeneration::class, Tree::class, TreeGroup::class, TreeSpecies::class, GalleryAlbum::class, GalleryImage::class, SiteStatistic::class] as $model) {
            Gate::policy($model, AdminPolicy::class);
        }
        Gate::policy(SchoolApplication::class, SchoolApplicationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        foreach ([Inquiry::class, TreeAdoption::class, MailOutbox::class] as $model) {
            Gate::policy($model, WorkflowPolicy::class);
        }
        Gate::policy(TreeMaintenanceRecord::class, MaintenancePolicy::class);
        RateLimiter::for('public-workflows', fn ($request) => Limit::perMinute(5)->by($request->ip().'|'.$request->route()->getName()));
        RateLimiter::for('secure-links', fn ($request) => Limit::perMinute(10)->by($request->ip().'|'.$request->route()->getName()));
        RateLimiter::for('school-applications', fn ($request) => Limit::perMinute(5)->by($request->ip()));
        foreach ([School::class, Tree::class, TreeGroup::class, TreeSpecies::class, GalleryAlbum::class, GalleryImage::class] as $model) {
            $model::observe(MediaObserver::class);
        }
    }
}
