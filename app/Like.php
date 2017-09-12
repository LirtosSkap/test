<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function likable()
    {
        return $this->morphTo();
    }
}
