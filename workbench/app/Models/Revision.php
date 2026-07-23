<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    public $table = 'revisions';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
        ];
    }
}
