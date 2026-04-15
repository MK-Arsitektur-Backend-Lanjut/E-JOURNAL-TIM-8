<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'year',
        'abstract',
        'tags',
        'file_path',
    ];

    protected $casts = [
        'tags' => 'array',
    ];
}
