<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;

/**
 * Class DeadlineService.
 *
 * @package TTU\Charon\Services
 */
class CharonDefenseLabService
{
    /**
     * Create the deadline with the given parameters.
     * The deadline array is the array gotten from the charon from request.
     *
     * @param  Charon  $charon
     * @param  array  $defenseLabs
     *
     * @return void
     */
    public function createCharonDefenseLab(Charon $charon, array $defenseLabs)
    {
        Log::info("Creating a defense lab: ", [$defenseLabs]);
        $charon->defenseLabs()->save(new CharonDefenseLab([
            'lab_id' => $defenseLabs['lab_id'],
            'charon_id' => $charon->id
        ]));
    }

}
