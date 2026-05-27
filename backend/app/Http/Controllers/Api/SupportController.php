<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    // Mengambil data tutorial untuk menu Support
    public function getTutorials()
    {
        $tutorials = Tutorial::latest()->get();
        return response()->json($tutorials, 200);
    }
}