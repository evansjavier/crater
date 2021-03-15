<?php

namespace Crater\Models;
use Crater\Models\CompanySetting;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'movement_date',
        'quantity',
        'item_id',
        'invoice_return_item_id',
        'invoice_item_id',
    ];


    protected $appends = [
        'formattedMovementDate',
        'absoluteQuantity',
        'type',
    ];

    public function getFormattedMovementDateAttribute($value)
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->item->company_id);
        return Carbon::parse($this->movement_date)->format($dateFormat);
    }

    public function getAbsoluteQuantityAttribute($value)
    {
        return ($this->quantity > 0) ? $this->quantity : -$this->quantity;        
    }

    public function getTypeAttribute($value)
    {
        return ($this->quantity > 0) ? 'in' : 'out';
    }

    public function item(){
        return $this->belongsTo(Item::class);
    }
}
