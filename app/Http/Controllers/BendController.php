<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category_model;
use App\Libs\Bendutils;

class BendController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
//        dd(auth()->user()->name);

        if (session()->get("verifyurcode") != true)
            return redirect('logout');

        $data = [];
        $data['nav'] = Bendutils::makeLnav();

        return view('manager.yield.index', $data);
    }

}
