<?php

namespace Tests\Unit\Services\Flows;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\Mock;
use TTU\Charon\Facades\MoodleCron;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\DefenseRegistration;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Services\Flows\BookStudentRegistration;
use TTU\Charon\Services\Flows\FindAvailableRegistrationTimes;
use Tests\TestCase;
use TTU\Charon\Tasks\ExpireBookedRegistrations;

class BookStudentRegistrationTest extends TestCase
{
    /** @var Mock|FindAvailableRegistrationTimes */
    private $findRegistrationTimes;

    /** @var Mock|DefenseRegistrationRepository */
    private $registrationRepository;

    /** @var Mock|MoodleCron */
    private $cron;

    /** @var BookStudentRegistration */
    private $flow;

    protected function setUp()
    {
        parent::setUp();

        $this->flow = new BookStudentRegistration(
             $this->findRegistrationTimes = Mockery::mock(FindAvailableRegistrationTimes::class),
             $this->registrationRepository = Mockery::mock(DefenseRegistrationRepository::class),
             $this->cron = Mockery::mock(MoodleCron::class)
        );
    }

    /**
     * @throws ValidationException
     */
    public function testRunBooksFirstAvailableTimes()
    {
        DefenseRegistration::unguard();

        Config::shouldReceive('get')
            ->with('app.defense_timeslot_minutes')
            ->andReturn(5);

        Config::shouldReceive('get')
            ->with('app.defense_booking_minutes')
            ->andReturn(15);

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('commit');
        DB::shouldReceive('statement');
        DB::shouldReceive('raw');

        $start = Carbon::parse('2020-12-15 12:10:00');
        $end = $start->copy()->addHour(3);

        $submission = new Submission();
        $submission->charon = new Charon(['defense_duration' => 10]);
        $submissions = collect([$submission]);

        $this->findRegistrationTimes
            ->shouldReceive('filterValidSubmissions')
            ->with(11, [13 => 17], $start, $end)
            ->once()
            ->andReturn($submissions);

        $this->findRegistrationTimes
            ->shouldReceive('validate')
            ->with(3, 11, $submissions)
            ->once();

        $this->registrationRepository
            ->shouldReceive('lock')
            ->with(true)
            ->once();

        $times = collect([
            new DefenseRegistration(['id' => 101, 'teacher_id' => 201]),
            new DefenseRegistration(['id' => 102, 'teacher_id' => 201]),
            new DefenseRegistration(['id' => 103, 'teacher_id' => 201]),
            new DefenseRegistration(['id' => 106, 'teacher_id' => 202]),
            new DefenseRegistration(['id' => 108, 'teacher_id' => 202]),
            new DefenseRegistration(['id' => 109, 'teacher_id' => 202]),
            new DefenseRegistration(['id' => 110, 'teacher_id' => 202]),
        ]);

        /** @var Mock|Builder $query */
        $builder = Mockery::mock(Builder::class);

        $this->registrationRepository
            ->shouldReceive('findAvailableTimesBetween')
            ->with(7, $start, $end)
            ->once()
            ->andReturn($times);

        $this->registrationRepository
            ->shouldReceive('query')
            ->once()
            ->andReturn($builder);

        $builder->shouldReceive('whereIn')
            ->with('id', [108, 109])
            ->once()
            ->andReturn($builder);

        $builder->shouldReceive('update')
            ->with([
                'student_id' => 11,
                'charon_id' => 13,
                'submission_id' => 17,
                'progress' => 'Booked'
            ])
            ->once();

        $this->cron
            ->shouldReceive('enqueue')
            ->with(ExpireBookedRegistrations::class, [], 905)
            ->once();

        $actual = $this->flow->run(3, 7, 11, 13, 17, $start, $end);

        $this->assertTrue($actual);
    }
}
