<?php

namespace Wotz\FilamentBrigadaCms\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Wotz\FilamentBrigadaCms\Models\Concerns\HandlesTranslatableDrafts;

class TranslatableArticle extends Model
{
    use HandlesTranslatableDrafts;
    use HasTranslations;

    protected $table = 'articles';

    protected $guarded = [];

    public $timestamps = false;

    /** @var array<int, string> */
    public array $translatable = ['title'];
}
