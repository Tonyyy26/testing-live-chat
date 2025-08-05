<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConversationType extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationTypeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['type'];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
