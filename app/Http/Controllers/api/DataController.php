<?php

namespace App\Http\Controllers\api;

use App\Models\Data;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataResource;

class DataController extends Controller
{
    public function index()
    {
        return  DataResource::collection(Data::all());
    }
}