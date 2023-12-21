<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Report extends Model
{
    use CrudTrait;
    use HasFactory;

    // Có thể điền vào
    protected $fillable = [
        'title',
        'description',
        'location_api',
        'location_text',
    ];

    protected $attributes = [
        'status' => ReportStatus::SENT,
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function medias() : BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'report_media', 'reports_id', 'media_id');
    }

    public function feedback() : HasMany
    {
        return $this->hasMany(Feedback::class, 'reports_id', 'id');
    }

    public function assignment() : HasOne
    {
        return $this->hasOne(Assignment::class, 'reports_id', 'id');
    }
}
