<?php
namespace Crater\Models;

use Illuminate\Database\Eloquent\Model;
use Crater\Models\Invoice;
use Crater\Models\Tax;
use Crater\Models\Item;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class InvoiceReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_return_id',
        'name',
        'item_id',
        'description',
        'company_id',
        'quantity',
        'price',
        'discount_type',
        'discount_val',
        'total',
        'tax',
        'discount'
    ];

    protected $casts = [
        'price' => 'integer',
        'total' => 'integer',
        'discount' => 'float',
        'quantity' => 'float',
        'discount_val' => 'integer',
        'tax' => 'integer'
    ];

    public function invoiceReturn()
    {
        return $this->belongsTo(InvoiceReturn::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    public function scopeWhereCompany($query, $company_id)
    {
        $query->where('company_id', $company_id);
    }

    public function scopeInvoicesBetween($query, $start, $end)
    {
        $query->whereHas('invoice', function ($query) use ($start, $end) {
            $query->whereBetween(
                'invoice_date',
                [$start->format('Y-m-d'), $end->format('Y-m-d')]
            );
        });
    }

    public function scopeApplyInvoiceFilters($query, array $filters)
    {
        $filters = collect($filters);

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('Y-m-d', $filters->get('from_date'));
            $end = Carbon::createFromFormat('Y-m-d', $filters->get('to_date'));
            $query->invoicesBetween($start, $end);
        }
    }

    public function scopeItemAttributes($query)
    {
        $query->select(
            DB::raw('sum(quantity) as total_quantity, sum(total) as total_amount, invoice_items.name')
        )->groupBy('invoice_return_items.name');
    }
}
