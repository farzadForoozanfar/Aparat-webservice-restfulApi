<?php

namespace App\Http\Controllers;

use App\Http\Requests\Video\ChangeStateRequest;
use App\Http\Requests\Video\FavoriteVideoListRequest;
use App\Http\Requests\Video\LikedVideoRequest;
use App\Http\Requests\Video\DeleteVideoRequest;
use App\Http\Requests\Video\CreateVideoRequest;
use App\Http\Requests\Video\LikeVideoRequest;
use App\Http\Requests\Video\ShowVideoCommentsRequest;
use App\Http\Requests\Video\ShowVideoRequest;
use App\Http\Requests\Video\RepublishVideoRequest;
use App\Http\Requests\Video\StatisticsVideoRequest;
use App\Http\Requests\Video\UnLikeVideoRequest;
use App\Http\Requests\Video\UpdateVideoRequest;
use App\Http\Requests\Video\UploadVideoBannerRequest;
use App\Http\Requests\Video\UploadVideoRequest;
use App\Services\VideoService;

class VideoController extends Controller
{
    public function Index(ShowVideoRequest $request)
    {
        return VideoService::list($request);
    }
    public function Upload(UploadVideoRequest $request)
    {
        return VideoService::uploadVideo($request);
    }

    public function Create(CreateVideoRequest $request)
    {
        return VideoService::createVideo($request);
    }

    public function Edit(UpdateVideoRequest $request)
    {
        return VideoService::updateVideo($request);
    }

    public function UploadBanner(UploadVideoBannerRequest $request)
    {
        return VideoService::uploadVideoBanner($request);
    }

    public function ChangeState(ChangeStateRequest $request)
    {
        return VideoService::changeState($request);
    }

    public function Republish(RepublishVideoRequest $request)
    {
        return VideoService::republish($request);
    }

    public function Like(LikeVideoRequest $request)
    {
        return VideoService::likeVideo($request);
    }

    public function UnLike(UnLikeVideoRequest $request)
    {
        return VideoService::unLikeVideo($request);
    }

    public function LikedByCurrentUser(LikedVideoRequest $request)
    {
        return VideoService::likedByCurrentUser($request);
    }

    public function Show(ShowVideoRequest $request)
    {
        return VideoService::showVideo($request);
    }

    public function Delete(DeleteVideoRequest $request)
    {
        return VideoService::deleteVideo($request);
    }

    public function Statistics(StatisticsVideoRequest $request)
    {
        return VideoService::statisticsVideo($request);
    }

    public function ShowComments(ShowVideoCommentsRequest $request)
    {
        return VideoService::showVideoComments($request);
    }

    public function Favorites(FavoriteVideoListRequest $request)
    {
        return VideoService::favoritesVideoList($request);
    }
}
