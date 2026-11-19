<?php

namespace App\Http\Controllers;

use App\Models\ModelShowroom;

class HomeController extends Controller
{
    public function index()
    {
        $showroomModels = ModelShowroom::active()->ordered()->get();

        return view('home', compact('showroomModels'));
    }
}
