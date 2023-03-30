<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
}
