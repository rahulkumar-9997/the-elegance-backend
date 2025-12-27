<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Flyer;
class FlyersController extends Controller
{
    public function index()
    {
        $flyersList = Flyer::orderBy('order')->paginate(20);
        return view('backend.pages.flyers.index', compact('flyersList'));
    }
}
