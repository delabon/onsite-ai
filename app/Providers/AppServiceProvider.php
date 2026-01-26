<?php

namespace App\Providers;

use App\Enums\MessageCategory;
use App\Services\Whatsapp\Handlers\ManualReviewHandler;
use App\Services\Whatsapp\Handlers\MaterialRequestHandler;
use App\Services\Whatsapp\Handlers\QuestionHandler;
use App\Services\Whatsapp\Handlers\SafetyIncidentHandler;
use App\Services\Whatsapp\Handlers\SiteNoteHandler;
use App\Services\Whatsapp\WorkflowRouter;
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
        $this->app->bind(WorkflowRouter::class, function ($app) {
            return new WorkflowRouter([
                MessageCategory::SafetyIncident->value => $app->make(SafetyIncidentHandler::class),
                MessageCategory::MaterialRequest->value => $app->make(MaterialRequestHandler::class),
                MessageCategory::Question->value => $app->make(QuestionHandler::class),
                MessageCategory::SiteNote->value => $app->make(SiteNoteHandler::class),
                MessageCategory::Other->value => $app->make(ManualReviewHandler::class),
                MessageCategory::Unknown->value => $app->make(ManualReviewHandler::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

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
