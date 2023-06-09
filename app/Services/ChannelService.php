<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Video;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChannelService extends BaseService
{

    public static function updateChannelInfo(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $channelId =  $request->route('id');
            if ($channelId)
            {
                $channel = Channel::findOrFail($channelId);
                $user    = $channel->user;
            }
            else
            {
                $user      = auth()->user();
                $channel   = $user->channel;
            }
            $channel->name = $request->name;
            $channel->info = $request->info;
            $user->website = $request->website;
            $channel->save();
            $user->save();
            DB::commit();
            return response(['message' => 'عملیات آپدیت کانال با موفقیت انجام شد'], 200);
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'خطایی رخ داده'], 500);
        }
    }

    public static function uploadAvatar4Channel(Request $request)
    {
        try
        {
            $banner   = $request->file('banner');
            $fileName = time() . md5(auth()->id()) . '_' . Str::random();
            Storage::disk('channel')->put($fileName, $banner->get());

            $channel  = auth()->user()->channel;
            if ($channel->banner)
            {
                Storage::disk('channel')->delete($channel->banner);
            }
            $channel->banner = Storage::disk('channel')->path($fileName);
            $channel->save();
            return response(['banner-url' => Storage::disk('channel')->url($fileName)], 200);
        }
        catch (Exception $exception)
        {
            Log::error($exception);
            return response(['message' => $exception->getMessage()], 500);
        }
    }

    public static function UpdateSocial(Request $request)
    {
        try
        {
            $socials = [
                'facebook'=> $request->input('facebook'),
                'telegram'=> $request->input('telegram'),
                'instagram'=> $request->input('instagram'),
                'twitter'=> $request->input('twitter')
            ];
            auth()->user()->channel->update(['socials' => json_encode($socials)]);
            return response(['message' => 'با موفقیت ثبت شد'], 200);
        }
        catch (Exception $exception)
        {
            Log::error($exception);
            return response(['message' => 'خطایی رخ داده ' . $exception->getMessage()], 500);
        }
    }

    public static function followChannel(Request $request)
    {
        try {
            $user = $request->user();
            $user->follow($request->channel->user);
            return response(['message' => 'با موفقیت دنبال شد'], 200);
        }
        catch (Exception $exception)
        {
            Log::error($exception);
            return response(['message' => 'خطا رخ داده است' . $exception->getMessage()], 500);
        }
    }

    public static function unFollowChannel(Request $request)
    {
        try {
            $user = $request->user();
            $user->unfollow($request->channel->user);
            return response(['message' => 'با موفقیت از لیست دنبال شوندگان حذف شد'], 200);
        }
        catch (Exception $exception)
        {
            Log::error($exception);
            return response(['message' => 'خطا رخ داده است' . $exception->getMessage()], 500);
        }
    }

    public static function statistics(Request $request)
    {
        //TODO نوشتن تابعی که تعداد کل کامنت ها در وضعیت تایید شده و رد شده و در انتظار نمایش بده
        $comments = Video::channelComments($request->user()->id)
                    ->selectRaw('comments.*')->count();
        $from_date = now()->subDays($request->get('last_n_days', 7))->toDateString();

        $data = [
            'data' => [],
            'total_views' => 0,
            'total_followers' => $request->user()->followers()->count(),
            'total_videos' => $request->user()->channelVideos()->count(),
            'total_republished' => $request->user()->republishedVideos()->count(),
            'total_comments' => $comments
        ];

        $videos = Video::views($request->user()->id)
            ->whereRaw("date(video_views.created_at) >= '{$from_date}'")
            ->selectRaw('date(video_views.created_at) as date, COUNT(*) as views')
            ->groupByRaw('date(video_views.created_at)')
            ->get()
            ->each(function ($item) use (&$data){
                $data['total_views'] += $item->views;
                $data['data'][$item->date] = $item->views;
        });

        return $data;
    }

}
