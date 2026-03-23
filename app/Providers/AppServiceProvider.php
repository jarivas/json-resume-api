<?php

namespace App\Providers;

use App\Models\Award;
use App\Models\Basic;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Interest;
use App\Models\Language;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Reference;
use App\Models\Skill;
use App\Models\Volunteer;
use App\Models\Work;
use App\Observers\ResumeModelObserver;
use App\Services\Chat\LlmClientInterface;
use App\Services\Chat\SdkLlmClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->registerModelObservers();
    }

    /**
     * Register model observers for resume data synchronization.
     */
    protected function registerModelObservers(): void
    {
        $models = [
            Work::class,
            Education::class,
            Skill::class,
            Project::class,
            Publication::class,
            Certificate::class,
            Award::class,
            Reference::class,
            Interest::class,
            Volunteer::class,
            Language::class,
            Basic::class,
        ];

        foreach ($models as $model) {
            $model::observe(ResumeModelObserver::class);
        }
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
