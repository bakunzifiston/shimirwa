<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventMedia extends Model
{
    protected $table = 'event_media';

    protected $fillable = ['event_id', 'type', 'path', 'caption', 'sort_order'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function url(): string
    {
        return asset($this->path);
    }

    public function isImage(): bool { return $this->type === 'image'; }
    public function isVideo(): bool { return $this->type === 'video'; }
}
