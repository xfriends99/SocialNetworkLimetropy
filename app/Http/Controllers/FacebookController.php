<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;

class FacebookController extends Controller
{
    protected $fb;

    public function __construct(LaravelFacebookSdk $fb)
    {
        $this->fb = $fb;
    }

    public function fanpage($id)
    {
        try {
            $response = $this->fb->get("/{$id}?fields=name,location,rating_count,fan_count,talking_about_count",env('FACEBOOK_APP_TOKEN'));
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()],404);
        }
        return response()->json(['error' => false,
            'data' =>array_merge((array)json_decode($response->getBody()),
                ['posts' =>$this->posts($id)])],200);
    }

    public function posts($id)
    {
        try {
            $response = $this->fb->get("/{$id}/posts?fields=created_time,message,shares,status_type",env('FACEBOOK_APP_TOKEN'));
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [];
        }
        $index = 0;
        $posts = [];
        foreach ((array)json_decode($response->getBody())->data as $post){
            $posts[$index] = $post;
            $posts[$index]->comments = $this->comments($post->id);
            $index++;
        }
        return $posts;
    }

    public function count_posts($id)
    {
        try {
            $response = $this->fb->get("/{$id}/posts?fields=created_time,message,shares,status_type",env('FACEBOOK_APP_TOKEN'));
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return [];
        }
        $array_final = [];
        $posts = collect((array)json_decode($response->getBody())->data);
        $date = Carbon::now();
        for($i = 1; $i<=30; $i++){
            $co = $posts->filter(function($item) use($date){
                return Carbon::parse($item->created_time)->format('Y-m-d')==$date->format('Y-m-d');
            });
            $array_final[$date->format('Y-m-d')] = $co->count();
            $date->subDay(1);
        }
        return response()->json(['error' => false,
            'data' =>$array_final],200);
    }

    public function like_posts($id)
    {
        try {
            $response = $this->fb->get("/{$id}/likes",env('FACEBOOK_APP_TOKEN'));
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()],404);
        }
        return response()->json(['error' => false,
            'data' =>(array)json_decode($response->getBody())],200);
    }

    public function comment_posts($id)
    {
        try {
            $response = $this->fb->get("/{$id}/comments",env('FACEBOOK_APP_TOKEN'));
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()],404);
        }
        return response()->json(['error' => false,
            'data' =>(array)json_decode($response->getBody())],200);
    }

    public function comments($id)
    {
        $response = $this->fb->get("/{$id}/comments",env('FACEBOOK_APP_TOKEN'));
        return (array)json_decode($response->getBody())->data;
    }

    public function insights($id)
    {
        try {
            $page_stories = $this->fb->get("/{$id}/insights/page_stories", env('FACEBOOK_APP_TOKEN'));
            $page_impressions = $this->fb->get("/{$id}/insights/page_impressions", env('FACEBOOK_APP_TOKEN'));
            $page_engaged_users = $this->fb->get("/{$id}/insights/page_engaged_users", env('FACEBOOK_APP_TOKEN'));
            $page_fans = $this->fb->get("/{$id}/insights/page_fans", env('FACEBOOK_APP_TOKEN'));
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()],404);
        }
        return response()->json(['error' => false,
            'data' =>['page_stories' => (array)json_decode($page_stories->getBody()),
                'page_impressions' => (array)json_decode($page_impressions->getBody()),
                'page_engaged_users' => (array)json_decode($page_engaged_users->getBody()),
                'page_fans' => (array)json_decode($page_fans->getBody())]],200);
    }
}
