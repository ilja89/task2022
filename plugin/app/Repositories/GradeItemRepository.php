<?php

namespace TTU\Charon\Repositories;

use Zeizig\Moodle\Models\GradeItem;

class GradeItemRepository
{
    /**
     * @param int $id
     * @return GradeItem
     */
    public function find(int $id)
    {
        return GradeItem::find($id);
    }
}
