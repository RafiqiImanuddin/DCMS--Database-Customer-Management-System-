<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SosmedKeep extends Model
{
    use HasFactory;

    protected $table = 'sosmed_keeps';

    protected $guarded = ['id'];

    protected $casts = [
        'keep_timestamp' => 'datetime',
        'tanggal_input_ar' => 'datetime',
    ];
}
