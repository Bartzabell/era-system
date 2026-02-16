<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotline;
use Illuminate\Http\Request;

class HotlineApiController extends Controller
{
    public function index()
    {
        $hotlines = Hotline::orderBy('hotline_name')->get();
        
        return response()->json([
            'success' => true,
            'hotlines' => $hotlines
        ]);
    }
}
