<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    const ADMIN_TYPE = 'admin';
    const USER_TYPE = 'user';
    const TYPES = [self::ADMIN_TYPE, self::USER_TYPE];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'name',
        'email',
        'password',
        'mobile',
        'avatar',
        'website',
        'verified_code',
        'verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'verified_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * find user o base email or mobile
     * @param $username
     */
    public function findForPassport($username)
    {
        try {
            return static::withTrashed()->where('mobile',$username)->orwhere('email',$username)->first();
        }
        catch (\Exception $exception){
            return response(['message' => $exception->getMessage()], 401);
        }
    }

    public function setMobileAttribute($value)
    {
        $this->attributes['mobile'] = toValidMobileNumber($value);
    }

    //region relations
    public function channel()
    {
        return $this->hasOne(Channel::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function playlist()
    {
        return $this->hasMany(PlayList::class);
    }

    public function channelVideos()
    {
        return $this->hasMany(Video::class)->selectRaw('*,0 as republished');
    }

    public function republishedVideos()
    {
        return $this->hasManyThrough(Video::class,
                                    RepublishVideo::class,
                                    'user_id', //video_republishes.user_id
                                    'id', // video.id
                                    'id',  // user.id
                                    'video_id')->selectRaw('videos.*,1 as republished'); //video_republishes.video_id
    }

    public function videos()
    {
        return $this->channelVideos()->union($this->republishedVideos());
    }

    public function favouriteVideos()
    {
        return $this->hasManyThrough(Video::class,
        VideoFavourite::class,
        'user_id', //VideoFavourite.user_id
        'id', // video.id
        'id',  // user.id
        'video_id');
    }

    public function followings()
    {
        return $this->hasManyThrough(
            User::class,
            UserFollowing::class,
            'user_id1',
            'id',
            'id',
            'user_id2'
        );
    }

    public function followers()
    {
        return $this->hasManyThrough(
            User::class,
            UserFollowing::class,
            'user_id2',
            'id',
            'id',
            'user_id1'
        );
    }

    public function views()
    {
        return $this->belongsToMany(Video::class, 'video_views')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    //endregion relations

    //region custom method
    public function isAdmin(): bool
    {
        return $this->type === self::ADMIN_TYPE;
    }

    public function isBaseUser(): bool
    {
        return $this->type === self::USER_TYPE;
    }

    public function follow(User $user)
    {
        UserFollowing::create([
            'user_id1' => $this->id,
            'user_id2' => $user->id
        ]);
        return $user;
    }

    public function unfollow(User $user)
    {
        return UserFollowing::where([
            'user_id1' => $this->id,
            'user_id2' => $user->id
        ])->delete();
    }
    //endregion custom method

    //region override method
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($user){
            $user->channelVideos()->delete();
            $user->playlist()->delete();
            $user->channel()->delete();
        });
        static::restoring(function ($user){
            $user->channelVideos()->restore();
            $user->playlist()->restore();
            $user->channel()->restore();
        });
    }
    //endregion
}
