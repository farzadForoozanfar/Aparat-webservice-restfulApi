<?php

namespace App\Http\Requests\Video;

use App\Rules\CategoryIdRule;
use App\Rules\UploadedVideoBannerId;
use App\Rules\UploadedVideoId;
use Illuminate\Foundation\Http\FormRequest;

class CreateVideoRequest extends FormRequest

{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'video_id'=>['required', new UploadedVideoId()],
            'title'=> 'required|string|max:255',
            'category'=> ['required', new CategoryIdRule(CategoryIdRule::PUBLIC_CATEGORIES)],
            'info'=> 'nullable|string',
            'tags'=> 'nullable|array',
            'tags.*'=>'exists:tags,id',
            'playlist'=> 'nullable|exists:playlist,id',
            'channel_category'=> ['nullable', new CategoryIdRule(CategoryIdRule::PRIVATE_CATEGORIES)],
            'banner'=> ['nullable', new UploadedVideoBannerId() ],
            'publish_at'=> 'nullable|date'
        ];
    }
}
