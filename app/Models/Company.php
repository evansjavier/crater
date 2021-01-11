<?php
namespace Crater\Models;

use Crater\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Crater\Models\User;
use Carbon\Carbon;
use Crater\Models\CompanySetting;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model implements HasMedia
{
    use InteractsWithMedia;

    use HasFactory;

    protected $fillable = ['name','nif', 'logo', 'unique_hash'];

    protected $appends=[
        'logo',
        'formattedCreatedAt',
    ];

    public function getLogoAttribute()
    {
        $logo = $this->getMedia('logo')->first();
        if ($logo) {
            return  asset($logo->getUrl());
        }
        return ;
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function settings()
    {
        return $this->hasMany(CompanySetting::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function getFormattedCreatedAtAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->id);
        return Carbon::parse($this->created_at)->format($dateFormat);
    }

    public function getAdminFormattedCreatedAtAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', auth()->user()->company->id);
        return Carbon::parse($this->created_at)->format($dateFormat);
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereSearch($query, $search)
    {
        foreach (explode(' ', $search) as $term) {
            $query->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', '%' . $term . '%');
            });
        }
    }

    public function scopeWhereDisplayName($query, $displayName)
    {
        return $query->where('name', 'LIKE', '%' . $displayName . '%');
    }

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            return collect(['data' => $query->get()]);
        }

        return $query->paginate($limit);
    }

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('display_name')) {
            $query->whereDisplayName($filters->get('display_name'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'name';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

}
