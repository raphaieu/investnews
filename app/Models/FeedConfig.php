<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['feed_id', 'enabled', 'interval_sec'])]
class FeedConfig extends Model
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'interval_sec' => 'integer',
        ];
    }
}
