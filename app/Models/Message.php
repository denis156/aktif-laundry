<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'target',
        'message',
        'url',
        'filename',
        'schedule',
        'typing',
        'delay',
        'countrycode',
        'file',
        'location',
        'fllowup',
    ];
}
