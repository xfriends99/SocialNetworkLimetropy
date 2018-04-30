<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Carbon\Carbon;

class TwitterTweetsUserTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($tweet)
    {
        return [
            'id' => $tweet['id'],
            'created_at' => Carbon::parse($tweet['created_at'])->format('d/m/Y'),
            'text' => $tweet['text'],
            'retweets' => $this->retweets($tweet['retweets'])
        ];
    }

    public function retweets($retweets)
    {
        $response = [];
        try{
            foreach ($retweets as $r){
                $response[] = [
                    'id' => $r->id,
                    'created_at' => Carbon::parse($r->created_at)->format('d/m/Y'),
                    'text' => $r->text
                ];
            }
        } catch(\Exception $e){
            return [];
        }
        return $response;
    }
}
