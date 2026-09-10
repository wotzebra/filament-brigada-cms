<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Oddvalue\LaravelDrafts\Concerns\HasDrafts;

class Page extends Model
{
    use HasDrafts;

    protected $guarded = [];
}
