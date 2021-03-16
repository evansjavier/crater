<?php

namespace Crater\Http\Controllers\V1\Movement;

use Crater\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Crater\Http\Requests;
use Crater\Models\{Item, Movement};

class MovementsController extends Controller
{
    /**
     * Create Movement.
     *
     * @param  Crater\Http\Requests\MovementsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Requests\MovementsRequest $request)
    {
        $movement =  new Movement($request->all());
        $item_id = $request->input('item_id');

        if($request->input('movement_type') == 'out'){
            $movement->quantity = -1 * $movement->quantity;
        }
        
        $item = Item::find($item_id);

        DB::transaction(function () use(&$movement, $item) {
            $movement->save();

            $query_stock = DB::table('movements')
                ->select(DB::raw('SUM(quantity) as stock'))
                ->where('item_id', $item->id)
                ->first();

            $item->stock = $query_stock->stock;
            $item->save();
        });

        return response()->json([
            'movement' => $movement
        ]);
    }

    /**
     * Lista de movimientos de un item
     */
    public function indexByItem(Request $request, $item_id){

        $movements = Movement::where('item_id', $item_id)
            ->orderByDesc('movement_date')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return $movements;
    }

    /**
     * Obtener un movimiento
     */
    public function show(Movement $movement){

        $movement->load([
            'item.unit'
        ]);

        return response()->json([
            'movement' => $movement
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Crater\Http\Requests\MovementsRequest $request
     * @param  \Crater\Models\Movement  $movement
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\MovementsRequest $request, Movement $movement)
    {
        
        
        
        DB::transaction(function () use(&$movement, $request) {
            $data = $request->validated();

            if($data['movement_type'] == 'out'){
                $data['quantity'] = -1 * $data['quantity'];
            }

            $movement->update($data);

            $query_stock = DB::table('movements')
                ->select(DB::raw('SUM(quantity) as stock'))
                ->where('item_id', $movement->item->id)
                ->first();

            $movement->item->stock = $query_stock->stock;
            $movement->item->save();
        });

        return response()->json([
            'movement' => $movement
        ]);
    }

    /**
     * Delete a list of existing Items.
     *
     * @param  \Crater\Models\Movement  $movement
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Movement $movement)
    {


        $item = $movement->item;

        DB::transaction(function () use(&$movement, $item) {
            $movement->delete();

            $query_stock = DB::table('movements')
                ->select(DB::raw('SUM(quantity) as stock'))
                ->where('item_id', $item->id)
                ->first();

            $item->stock = $query_stock->stock;
            $item->save();
        });

        return response()->json([
            'success' => true
        ]);
    }

}
