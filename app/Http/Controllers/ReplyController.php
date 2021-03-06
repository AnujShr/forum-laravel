<?php

namespace App\Http\Controllers;

use App\reply;
use App\Thread;
use Illuminate\Http\Request;

class ReplyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',['except'=>'index']);
    }

    public function index($channelId, Thread $thread)
    {
        return $thread->replies()->paginate(4);
    }
    /**
     * @param $channel
     * @param Thread $thread
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store($channel,Thread $thread)
    {
        $this->validate(request(),[
            'body' => 'required'
        ]);
       $reply = $thread->addReply([
            'body' => request('body'),
            'user_id' => auth()->id()
        ]);
        if(request()->expectsJson()){
            return $reply->load('owner');
        }
        return back()
            ->with('flash','Your Reply has been left');
    }

    public function destroy(Reply $reply)
    {
        $this->authorize('update', $reply);
        $reply->delete();
        if(request()->expectsJson()){
            return response(['status'=> 'Reply deleted']);
        }
        return back();
    }
    public function update(Reply $reply)
   {
        $this->authorize('update', $reply);

        $this->validate(request(), ['body' => 'required']);

        $reply->update(request(['body']));
    }
}
