<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class welcome extends Controller
{
    public function welcome(Request $request){
     


        return view('welcome', ["variable" => '']);
      
    }

   
}
