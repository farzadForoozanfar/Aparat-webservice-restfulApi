<?php

namespace App\Jobs;

use App\Models\Video;
use FFMpeg\Filters\Video\CustomFilter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Media;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class AddFilter2UploadedVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $videoSlug;
    private string|int $video_id;
    private $user;
    private bool $enable_watermark;

    /**
     * Create a new job instance.
     * @param string $videoSlug
     * @param $video_id
     * @param bool $enable_watermark
     */
    public function __construct(string $videoSlug, $video_id, bool $enable_watermark)
    {
        $this->videoSlug = $videoSlug;
        $this->video_id  = $video_id;
        $this->user      = auth()->user();
        $this->enable_watermark = $enable_watermark;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tmpPath = '/tmp/' . $this->video_id;
        $video = Video::where(['slug' => $this->videoSlug]);
        if ($video->count())
        {
            $channelName   = $this->user->channel->name;
            /** @var Media $videoFile */
            $videoFile     = FFMpeg::fromDisk('video')->open($tmpPath);
            $format        = new \FFMpeg\Format\Video\X264('libmp3lame');
            if ($this->enable_watermark)
            {
                $filter = new CustomFilter("drawtext=text='aparat.me/{$channelName}': fontcolor=blue: fontsize=24:
                         box=1: boxcolor=white@0.4: boxborderw=5:
                         x=10: y=(h - text_h - 10)");
                $videoFile = $videoFile->addFilter($filter);
            }
            $videoFilter = $videoFile->export()->toDisk('video')->inFormat($format);
            $videoFilter->save($this->user->id . '/' .$video->slug . '.mp4');
            Storage::disk('video')->delete($tmpPath);
           $video->duration = $videoFile->getDurationInSeconds();
           $video->state = Video::CONVERTED;
           $video->save();
        }
        Storage::disk('video')->delete($tmpPath);
    }



}
