<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    public $table = 'posts';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
        ];
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class, 'revision_id');
    }
}
