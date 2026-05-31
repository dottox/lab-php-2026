<?php

namespace App\Models\User;

use App\Models\Company\Company;
use App\Models\Service\Service;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('user_id', 'bio', 'avg_rating', 'total_reviews', 'is_verified')]
#[Hidden('created_at', 'updated_at', 'deleted_at')]
class ProfessionalProfile extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $casts = [
        'avg_rating' => 'float',
        'reviews_count' => 'integer',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'professional_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function companies()
    {
        return $this->hasMany(Company::class, 'professional_id');
    }
}
