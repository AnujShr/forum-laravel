<?php

namespace App;

use App\Channel;
use App\ThreadSubscriptions;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Thread
 * @package App
 */
class Thread extends Model
{
    use RecordActivity;
    /**
     * @var array
     */
    protected $guarded = [];
    /**
     * @var array
     */
    protected $with = ['owner', 'channel'];
    protected $appends = ['isSubscribedTo'];

    /**
     *
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($thread) {
            $thread->replies->each->delete();
        });
    }


    /**
     * @return string
     */
    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->id}";
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
//            ->withCount('favourites');

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param $reply
     * @return Model
     */
    public function addReply($reply)
    {
        $reply = $this->replies()->create($reply);

        $this->subscriptions->filter(function ($sub) use ($reply) {
                return $sub->user_id != $reply->user_id;
            })->each->notify($reply);

        return $reply;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    /**
     * @param $query
     * @param $filters
     * @return mixed
     */
    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);

    }


    /**
     * @param null $userId
     */
    public function subscribe($userId = null)
    {
        $this->subscriptions()->create(['user_id' => $userId ?: auth()->id()]);

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscriptions::class);
    }

    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()->where('user_id', auth()->id())->exists();
    }

    /**
     * @param null $userId
     */
    public function unsubscribe($userId = null)
    {
        $this->subscriptions()->where('user_id', $userId ?: auth()->id())->delete();
    }
}
