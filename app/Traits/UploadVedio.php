<?php

namespace App\Traits;

use File;
use Illuminate\Support\Str;

trait UploadVedio
{
    public function upload_vedio($video, $path)
    {
        $mime_type = $video->getClientMimeType();
        $mime_to_ext = [
            'video/mp4' => 'mp4',
            'video/x-matroska' => 'mkv',
            'video/webm' => 'webm',
            // Add more mappings as needed
        ];
        if (!array_key_exists($mime_type, $mime_to_ext)) {
            return $this->send_response(400, 'تنسيق الفيديو غير مدعوم', [], ["تنسيق الفيديو غير مدعوم"]);
        }

        $extension = $mime_to_ext[$mime_type];
        $filename = time() . Str::random(2) . '.' . $extension;

        if (!file_exists(public_path() . $path)) {
            File::makeDirectory(public_path() . $path, 0755, true);
        }
        $video->move(public_path() . $path, $filename);

        return $path . $filename;
    }
}
