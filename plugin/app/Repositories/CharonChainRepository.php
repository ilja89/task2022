<?php

namespace TTU\Charon\Repositories;

use Illuminate\Database\Eloquent\Builder;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonChain;

class CharonChainRepository {

    public function __construct() {}

    /**
     * @return Builder|CharonChain
     */
    public function query()
    {
        return CharonChain::query();
    }

    /**
     * Save the Charon instance.
     *
     * @param CharonChain $charonChain
     *
     * @return boolean
     */
    public function save($charonChain)
    {
        return $charonChain->save();
    }

    public function getCharonChainById($id)
    {
        return CharonChain::find($id);
    }

    public function getNextChain($chain)
    {
        $next = CharonChain::where('master_charon_id', $chain->master_charon_id)->where('charon_nr', $chain->charon_nr + 1)->get();
        if ($next->isEmpty()) {
            return null;
        } else {
            return $next[0];
        }
    }

    public function getPreviousChain($chain) {
        $next = CharonChain::where('master_charon_id', $chain->master_charon_id)->where('charon_nr', $chain->charon_nr - 1)->get();
        if ($next->isEmpty()) {
            return null;
        } else {
            return $next[0];
        }
    }

}
