<?php

namespace App\Http\Controllers;

use App\Models\Batsman;
use App\Models\Bowler;
use App\Models\Log;
use App\Models\Team1;
use App\Models\Team2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScorerController extends Controller
{
    public static $batsmen; //To keep track of Teams batsmen

    public static $bowlers; //To keep track of Teams bowlers
    public $num;

    public function start($bat)
    {
        $this->num = $bat;
        $vaildator = Validator::make(
            ['bat' => $bat],
            ['bat' => 'required|numeric|between:1,2'],
            ['bat' => 'URL parameter denotes Which team decided to bat and should be either 1 or 2']
        );

        if ($vaildator->fails())
            return response()->json([
                "message" => $vaildator->errors(),
            ], 422);

        Log::truncate(); //This will clear the previous match records

        // return response()->json([
        //     "message"=>"aMatch will start soon!"
        // ],200);

        return $this->start_inning($bat);
    }

    public function start_inning($num)
    {

        //Load the batsman and bowlers
        if ($num == 1) {

            $batsmen = Team1::all()->toArray();

            $bowlers = Team2::all()->toArray();
        } else {

            $batsmen = Team2::all()->toArray();
            $bowlers = Team1::all()->toArray();
        }

        Batsman::truncate();
        foreach ($batsmen as $batsman) {
            Batsman::create(['name' => $batsman['player']]);
        }

        Bowler::truncate();
        foreach ($bowlers as $bowler) {
            Bowler::create(['name' => $bowler['player']]);
        }
        return response()->json([
            "message" => "Team $num is batting"
        ], 200);
    }

    public function add_score(Request $request)
    {
        $latestLog = Log::orderBy('created_at', 'desc')->first();
        $current_over = $latestLog ? $latestLog->current_over : 1;

        if ($current_over <= 5) {
            $validator = Validator::make($request->all(), [
                'ball' => 'required|in:0,1,2,3,4,5,6,7,noball,wide,wicket',
                'batsman_run' => 'required|in:0,1,2,3,4,6,out'
            ], [
                'ball.required' => 'The ball field is required.',
                'ball.in' => 'The ball field must be one of: 0, 1, 2, 3, 4, 6, noball, wide,wicket!',
                'run.required' => 'The run field is required.',
                'run.in' => 'The run field must be one of: 0, 1, 2, 3, 4, 6.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }

            // Get the batsman who is currently facing the ball
            $current_batsman = $this->select_batsman(); //  selecting the batsman based on their ID
            // Get the current bowler
            $current_bowler = $this->select_bowler();

            $latestCount = Log::orderBy('created_at', 'desc')->first();

            $current_wickets = $latestLog ? $latestLog->current_wickets : 0;
            $current_runs = $latestLog ? $latestLog->current_runs : 0;

            if ($latestCount == !null) {
                $latestCount = $latestCount->count;
                if ($latestCount == 6) {
                    $latestCount = 0;
                    $current_over++;
                    Bowler::where('name', $current_bowler)
                        ->update([
                            'runs' => $current_runs, 'over' => $current_over - 1, 'wickets' => $current_wickets
                        ]);
                    $current_bowler = $this->select_bowler();
                }
            } else {
                $latestCount = 0;
            }
            if ($request->ball != 'noball') {
                if ($request->ball != 'wide')
                    $latestCount++;
            }


            switch ($request->ball) {
                case 1:
                    $run = 1;
                    break;
                case 2:
                    $run = 2;
                    break;
                case 3:
                    $run = 3;
                    break;
                case 4:
                    $run = 4;
                    break;
                case 6:
                    $run = 6;
                    break;
                case 'noball':
                    $run = 1 + $request->batsman_run;
                    break;
                case 'wide':
                    $run = 1;
                    break;
                default:
                    $run = 0;
                    break;
            }

            //logic when a wicket is taken....
            if ($request->ball == 'wicket') {
                // dd($current_batsman);
                $hisruns = Log::where('batsman', $current_batsman)->pluck('hisruns')->toArray();
                $total_runs = array_sum($hisruns);

                // Calculate How many ball does he played!!
                $total_balls = Log::where('batsman', $current_batsman)->count();
                $iswicket = true;
                $current_wickets++;
                Batsman::where('name', $current_batsman)
                    ->update(['runs' => $total_runs, 'balls' => $total_balls]); //Log that particular batsman's info in batsmen table
            } else {
                $iswicket = false;
            }
            // Create a new log entry
            if ($current_over <= 5) {
                Log::create([
                    'batsman' => $current_batsman, //Who is hitting
                    'isout' => $iswicket,
                    'hisruns' => $request->input('batsman_run'),
                    'bowler' => $current_bowler,
                    'onthisbowl' => $request->input('ball'),
                    'current_runs' => $run + $current_runs,
                    'current_wickets' => $current_wickets,
                    'current_over' => $current_over,
                    'count' =>  $latestCount
                ]);
                if ($iswicket) {
                    $current_batsman = $this->select_batsman();
                }
                return response()->json([
                    'message' => 'Runs added successfully.'
                ], 200);
            } else {
                dd($this->num);
                return response()->json([
                    "message" => "The overs has completed please hit start inning api!!"
                ], 200);
            }

            // Optionally, update the batsman and bowler based on the outcome of the ball (e.g., if the batsman gets out)

        } else {
            return $this->start_inning($this->num);
        }
    }

    public function select_batsman()
    {
        // dd('here ');
        return Batsman::where('balls', 0)->first()->name;
    }

    public function select_bowler()
    {
        return Bowler::where('over', 0)->first()->name;
    }

    public function undo()
    {
        $latestlog = Log::orderBy('created_at', 'desc')->first();
        if ($latestlog) {
            $latestlog->delete();
            return response()->json(["message" => "deleted successfully"], 200);
        } else {
            return response()->json(["message" => "Nothing to delete"], 200);
        }
    }
}
