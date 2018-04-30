<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Thujohn\Twitter\Facades\Twitter;
use App\Transformers\TwitterTweetsUserTransformer;
use App\Transformers\TwitterMentionsUserTransformer;
use Carbon\Carbon;

class TwitterController extends Controller
{
    public function user($id)
    {
        try {
            $response = Twitter::getUsers(['screen_name' => $id, 'format' => 'json']);
        } catch(\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()],404);
        }
        $data = (array)json_decode($response);
        $response = [
            'id' => $data['id'],
            'name' => $data['name'],
            'followers_count' => $data['followers_count'],
            'friends_count' => $data['friends_count'],
            'listed_count' => $data['listed_count'],
            'statuses_count' => $data['statuses_count'],
        ];

        return response()->json(['error' => false,
            'data' =>$response],200);
    }

    public function userTweets($id)
    {
        try {
            $response = Twitter::getUserTimeline(['screen_name' => $id, 'format' => 'json']);
        } catch(\Exception $e) {
            return [];
        }
        $index = 0;
        $posts = [];
        foreach (json_decode($response) as $tweet){
            $posts[$index] = (array)$tweet;
            $posts[$index]['retweets'] = $this->userRetweets($tweet->id);
            $index++;
        }
        $fractal = fractal($posts, new TwitterTweetsUserTransformer);
        $fractal->addMeta(['error' => false]);

        $response = $fractal->toArray();

        return response()->json($response,200);
    }

    public function userTweetsCount($id)
    {
        try {
            $response = Twitter::getUserTimeline(['screen_name' => $id, 'format' => 'json', 'count'=>200]);
        } catch(\Exception $e) {
            return [];
        }
        $array_final = [];
        $posts = collect((array)json_decode($response));
        $date = Carbon::now();
        $count = 0;
        for($i = 1; $i<=30; $i++){
            $co = $posts->filter(function($item) use($date){
                return Carbon::parse($item->created_at)->format('Y-m-d')==$date->format('Y-m-d');
            });
            $array_final[$date->format('Y-m-d')] = $co->count();
            $count+= $co->count();
            $date->subDay(1);
        }
        return response()->json(['error' => false,
            'data' =>$array_final, 'count' => $count],200);
    }

    public function userMentions($id)
    {
        try {
            $response = Twitter::getMentionsTimeline(['screen_name' => $id, 'format' => 'json', 'count'=>200]);
        } catch(\Exception $e) {
            return [];
        }
        $index = 0;
        $posts = [];
        foreach (json_decode($response) as $tweet){
            $posts[$index] = (array)$tweet;
            $posts[$index]['retweets'] = $this->userRetweets($tweet->id);
            $index++;
        }
        $fractal = fractal($posts, new TwitterMentionsUserTransformer);
        $fractal->addMeta(['error' => false]);

        $response = $fractal->toArray();

        return response()->json($response,200);
    }

    public function userMentionsCount($id)
    {
        try {
            $response = Twitter::getMentionsTimeline(['screen_name' => $id, 'format' => 'json', 'count'=>200]);
        } catch(\Exception $e) {
            return [];
        }
        $array_final = [];
        $posts = collect((array)json_decode($response));
        $date = Carbon::now();
        $count = 0;
        for($i = 1; $i<=30; $i++){
            $co = $posts->filter(function($item) use($date){
                return Carbon::parse($item->created_at)->format('Y-m-d')==$date->format('Y-m-d');
            });
            $array_final[$date->format('Y-m-d')] = $co->count();
            $count+= $co->count();
            $date->subDay(1);
        }
        return response()->json(['error' => false,
            'data' =>$array_final, 'count' => $count],200);
    }

    public function retweets($post_id)
    {
        try {
            $response = Twitter::getRts($post_id);
        } catch(\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()],404);
        }
        return response()->json(['error' => false,
            'data' =>json_decode($response)]);
    }

    public function userRetweets($post_id)
    {
        try {
            $response = Twitter::getRts($post_id);
        } catch(\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()],404);
        }
        return $response;
    }

}
