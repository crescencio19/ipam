<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommandModel;
use App\Models\IpModel;

class GenerateController extends Controller
{
    public function index()
    {
        $commands = CommandModel::where('isdeleted', 0)->get();
        $ips = IpModel::where('isdeleted', 0)->get();
        return view('generate.generate', compact('commands', 'ips'));
    }
}
