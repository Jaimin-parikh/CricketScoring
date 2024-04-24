<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Throwable;

class ViewrController extends Controller
{
    public function current_score()
    {
        try {
            $latestlog = Log::orderBy('created_at', 'desc')->first();
            $current_runs = $latestlog->current_runs;
            $current_wickets = $latestlog->current_wickets;

            switch ($latestlog->count) {
                case 6:
                    $current_over = ++$latestlog->current_over;
                    // dd($current_over);
                    break;
                default:
                    $current_over = "$latestlog->current_over . $latestlog->count";
            }
            return response()->json([
                "message" => [
                    "current_runs" => $current_runs,
                    "current_wickets" => $current_wickets,
                    "current_over" => $current_over,
                ]
            ], 200);
        } catch (Throwable $th) {
            return response()->json([
                "message" => "Match has not started Yet!!"
            ], 200);
        }
    }
    public function score_card()
    {
        return response()->json([
            "message" => Log::get(['batsman', 'hisruns', 'bowler', 'onthisbowl', 'count', 'current_runs', 'current_wickets', 'current_over'])->toArray()
        ], 200);
    }
}
"Jaxx was here";