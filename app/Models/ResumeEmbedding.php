<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeEmbedding extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'resume_embeddings';

    protected $fillable = [
        'model_type',
        'model_id',
        'content',
        'vector',
        'vector_length',
        'embedding_model',
    ];

    protected $casts = [
        'vector' => 'array',
    ];
}
