<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    public function index()
    {
        $historial = DB::table('price_predictions')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('history', ['historial' => $historial]);
    }

    public function show($id)
    {
        $pred = DB::table('price_predictions')->where('id', $id)->first();
        if (! $pred) abort(404);
        return view('price_prediction', ['pred' => $pred]);
    }
}
