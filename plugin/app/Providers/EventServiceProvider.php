<?php

namespace TTU\Charon\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'TTU\Charon\Events\CharonCreated' => [
            'TTU\Charon\Listeners\SendAddProjectInfoToTester',
            'TTU\Charon\Listeners\AddDeadlinesToCalendar',
        ],
        'TTU\Charon\Events\CharonUpdated' => [
            'TTU\Charon\Listeners\SendAddProjectInfoToTester',
            'TTU\Charon\Listeners\UpdateCalendarDeadlines',
        ],
        'TTU\Charon\Events\GitCallbackReceived' => [
            'TTU\Charon\Listeners\ForwardGitCallbackToTester',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
