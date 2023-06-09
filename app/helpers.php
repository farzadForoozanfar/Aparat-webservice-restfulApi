
<?php

use Hashids\Hashids;

if (!function_exists('toValidMobileNumber'))
{
    /**
     * تبدیل شماره تلفن به پیشوندی +98
     *
     * @param string $mobile
     * @return string
     */
    function toValidMobileNumber(string $mobile): string
    {
        return '+98' . substr($mobile, -10, 10);

    }
}

if (!function_exists('createVerifyCode'))
{
    /**
     * generate random code for verification
     *
     * @return int
     */
    function createVerifyCode(): int
    {
        return rand(100000,900000);
    }
}
if (!function_exists('uniqueId')) {
    function uniqueId(int $value): string
    {
        $hash = new Hashids(env('APP_KEY'), 10);
        return $hash->encode($value);
    }
}
if (!function_exists('clear_storage')) {
    function clear_storage(string $storageName)
    {
        try {
            Storage::disk($storageName)->delete(Storage::disk($storageName)->allFiles());
            foreach (Storage::disk($storageName)->allDirectories() as $dir) {
                Storage::disk($storageName)->deleteDirectory($dir);
            }
            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }
}
if (!function_exists('clientIP')) {
    /**
     * @param bool $withDate
     * @return string
     */
    function clientIP(bool $withDate = false): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] . '_' . md5($_SERVER['HTTP_USER_AGENT']);
        if($withDate)
            $ip .= '-' .now()->toDateString();
        return $ip;
    }
}
if (!function_exists('sort_comments')) {
    function sort_comments($comments, $parentId = null) {
        $result = [];

        foreach ($comments as $comment) {
            if ($comment->parent_id === $parentId) {
                $data = $comment->toArray();
                $data['children'] = sort_comments($comments, $comment->id);
                $result[] = $data;
            }
        }

        return $result;
    }
}
