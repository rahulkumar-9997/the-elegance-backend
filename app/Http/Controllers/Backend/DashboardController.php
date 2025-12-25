<?php
namespace App\Http\Controllers\Backend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class DashboardController extends Controller
{
    public function index(){
        $data = [
            'totalBanner'   => 0,
            'totalAwards'  => 0,
            'totalGallery' => 0,
            'totalCourses' => 0,
        ];
        return view('backend.pages.dashboard.index',  compact('data'));
    }

    
}
