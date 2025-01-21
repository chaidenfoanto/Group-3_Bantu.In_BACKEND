<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Fact;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function getVideos()
    {
        return response()->json(Video::all());
    }

    public function getFacts()
    {
        return response()->json(Fact::all());
    }

    public function getFactDetail($id)
    {
        $fact = Fact::find($id);

        if (!$fact) {
            return response()->json(['message' => 'Fact not found'], 404);
        }

        return response()->json($fact);
    }
}
