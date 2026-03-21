<?php

namespace App\Providers;

use App\Services\Chat\LlmClientInterface;
use App\Services\Chat\SdkLlmClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Models\Work;
use App\Models\Education;
use App\Models\Skill;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Certificate;
use App\Models\Award;
use App\Models\Reference;
use App\Models\Interest;
use App\Models\Volunteer;
use App\Models\Language;
use App\Models\Basic;
use App\Observers\ResumeModelObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LlmClientInterface::class, SdkLlmClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        // Register observer to update embeddings when resume-related models are saved.
        Work::observe(ResumeModelObserver::class);
        Education::observe(ResumeModelObserver::class);
        Skill::observe(ResumeModelObserver::class);
        Project::observe(ResumeModelObserver::class);
        Publication::observe(ResumeModelObserver::class);
        Certificate::observe(ResumeModelObserver::class);
        Award::observe(ResumeModelObserver::class);
        Reference::observe(ResumeModelObserver::class);
        Interest::observe(ResumeModelObserver::class);
        Volunteer::observe(ResumeModelObserver::class);
        Language::observe(ResumeModelObserver::class);
        Basic::observe(ResumeModelObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
