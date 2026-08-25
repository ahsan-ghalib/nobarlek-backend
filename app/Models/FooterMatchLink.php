<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterMatchLink extends Model
{
    protected $fillable = [
        'title',
        'url',
        'column',
        'sort_order',
        'is_active',
        'open_in_new_tab',
    ];

    protected $casts = [
        'column' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'open_in_new_tab' => 'boolean',
    ];
}
