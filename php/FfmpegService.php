<?php

namespace App\Services;

use App\Services\FileService;
use Illuminate\Support\Facades\Log;


class FfmpegService
{

    protected static function commandOutputFile($file_name, $row_id = 0) : string {
        if (!$row_id) {
            $row_id = time();
        }
        $file_path = storage_path('logs/videoCommand/' . $row_id . '/' . $file_name);
        $folder_path = dirname($file_path);
        FileService::createFolder($folder_path);
        return $file_path;
    }

    public static function writeTextOnImage($input_path, $output_path, $font_file, $text, $row_id = 0) {
        FileService::removeIfFileExists($output_path);
        $command = 'ffmpeg -i ' . $input_path . ' -vf "drawtext=fontfile= ' . $font_file . ':fontsize=60:fontcolor=black:x=(w-text_w)/2:y=((h-text_h)/2 + 80):text= '. $text. '" '. $output_path . ' 2> ' .  self::commandOutputFile('writeTextOnImage.txt', $row_id);
        CommonService::shellExe($command);
    }

    public static function generateNameImage($input_path, $output_path, $text, $font_file, $row_id = 0) {
        FileService::removeIfFileExists($output_path);
        $text_line_1 = '';
        $text_line_2 = '';
        if (strlen($text) > 25) {
            $text_arr = explode(" ", $text);
            list($text_arr1, $text_arr2)  = array_chunk($text_arr, ceil(count($text_arr) / 2));
            $text_line_1 = implode(' ', $text_arr1);
            $text_line_2 = implode(' ', $text_arr2);
        }

        if (!empty($text_line_1) && !empty($text_line_2)) {
            $input_path = public_path() . DIRECTORY_SEPARATOR .'banner'. DIRECTORY_SEPARATOR . 'name_text_bg_multiline.png';
            $command = 'ffmpeg -i ' . $input_path . ' -vf "drawtext=fontfile=' . $font_file . ':text='. $text_line_1. ':fontsize=35:fontcolor=black:x=(w-text_w)/2:y=(((h-text_h)/2)-25),drawtext=fontfile=' . $font_file . ':text='. $text_line_2. ':fontsize=35:fontcolor=black:x=(w-text_w)/2:y=(((h-text_h)/2)+25)" '. $output_path . ' 2> ' .  self::commandOutputFile('generateNameImage.txt', $row_id);
        }
        else {
            $command = 'ffmpeg -i ' . $input_path . ' -vf "drawtext=fontfile=' . $font_file . ':text='. $text. ':fontsize=35:fontcolor=black:x=(w-text_w)/2:y=((h-text_h)/2)" '. $output_path . ' 2> ' .  self::commandOutputFile('generateNameImage.txt', $row_id);
        }

        CommonService::shellExe($command);
    }

    public static function imageToVideo($input_path, $output_path, $frame_rate = 23.98, $row_id = 0) {
        FileService::removeIfFileExists($output_path);
        // $command = 'ffmpeg -r 1/5 -i '. $input_path .' -c:v libx264 -vf "pad=ceil(iw/2)*2:ceil(ih/2)*2" -pix_fmt yuv420p '. $output_path . ' 2> ' . self::commandOutputFile('imageToVideo.txt', $row_id);

        $frame_rate_in_command = '';
        if ($frame_rate) {
            $frame_rate_in_command = ' -r '. $frame_rate .' ';
        }
        $command = 'ffmpeg -framerate 1 -i '. $input_path .' -c:v libx264 -vf "pad=ceil(iw/2)*2:ceil(ih/2)*2" ' . $frame_rate_in_command .' -pix_fmt yuv420p '. $output_path . ' 2> ' . self::commandOutputFile('imageToVideo.txt', $row_id);
        CommonService::shellExe($command);
    }

    public static function concatVideo($video_paths, $output_video, $video_txt_file_path, $frame_rate = 23.98, $row_id = 0) {
        $video_txt_file_path = self::writeIntoVideoTxt($video_txt_file_path, $video_paths);
        FileService::removeIfFileExists($output_video);
        // $video_concat_command = 'ffmpeg -f concat -safe 0 -i '. $video_txt_file_path .' -c copy  '. $output_video . ' 2> ' . self::commandOutputFile('concatVideo.txt', $row_id);
        $video_concat_command = 'ffmpeg -f concat -safe 0 -i '. $video_txt_file_path .' -vf "fps=' . $frame_rate . '"  '. $output_video . ' 2> ' . self::commandOutputFile('concatVideo.txt', $row_id);
        CommonService::shellExe($video_concat_command);
    }

   public static function addPeriodicText($input_video, $output_video, $text, $font_file, $row_id = 0) {
        FileService::removeIfFileExists($output_video);
        // $command = "ffmpeg -y -r 30 -i $input_video -vf drawtext=fontfile=\"$font_file\":text=\"$text\":fontsize=20:fontcolor=black:x=w-tw-10:y=10:box=1:boxcolor=white@0.4:boxborderw=5:alpha='if(lt(mod(t,10),5),1,0)' $output_video 2> ". self::commandOutputFile('addPeriodicText.txt', $row_id);
        $command = 'ffmpeg -i '. $input_video .' -vf "drawtext=fontfile=' . $font_file . ':text='. $text. ':fontsize=20:fontcolor=black:x=w-tw-100:y=100:box=1:boxcolor=white@0.4:boxborderw=5:alpha=\'if(lt(mod(t,10),5),1,0)\'" -codec:a copy ' . $output_video . ' 2> ' .  self::commandOutputFile('addPeriodicText.txt', $row_id);
        CommonService::shellExe($command);
   }

    public static function addNamBannerToVideo($input_video, $input_banner, $output_video, $row_id = 0) {
        FileService::removeIfFileExists($output_video);
        // $command = 'ffmpeg -i '. $input_video .' -i ' . $input_banner . ' -filter_complex "overlay=W-w:0:enable=\'if(lt(mod(t,10),5),1,0)\'" ' . $output_video . ' 2> ' .  self::commandOutputFile('addNamBannerToVideo.txt', $row_id);
        $command = 'ffmpeg -i '. $input_video .' -i ' . $input_banner . ' -filter_complex "[0:v][1:v] overlay=W-w:0:enable=\'if(lt(mod(t,300),10),1,0)\'" ' . $output_video . ' 2> ' .  self::commandOutputFile('addNamBannerToVideo.txt', $row_id);
        CommonService::shellExe($command);
    }

    public static function setAudioTrack($input_path, $output_path, $row_id = 0) {
        FileService::removeIfFileExists($output_path);
        $command = 'ffmpeg -i ' . $input_path . ' -f lavfi -i aevalsrc=0 -shortest -y '. $output_path . ' 2> ' .  self::commandOutputFile('setAudioTrack.txt', $row_id);
        CommonService::shellExe($command);
    }

    public static function cutVideo($input_video, $output_video, $duration_in_second, $row_id = 0) {
        FileService::removeIfFileExists($output_video);
        $total_duration_in_second = FfmpegService::getTotalVideoDurationInSecond($input_video);
        $total_duration_in_minute = intval($total_duration_in_second / 60);
        if ($total_duration_in_minute > 10) {
            $command = 'ffmpeg -ss 00:09:00 -to 00:09:' . $duration_in_second . ' -i '. $input_video .' -acodec copy -vcodec copy ' . $output_video . ' 2> ' .  self::commandOutputFile('cutVideo.txt', $row_id);
        }
        else {
            $command = 'ffmpeg -ss 00:00:00 -to 00:00:' . $duration_in_second . ' -i '. $input_video .' -acodec copy -vcodec copy ' . $output_video . ' 2> ' .  self::commandOutputFile('cutVideo.txt', $row_id);
        }
        CommonService::shellExe($command);
    }

    public static function addBannerOnCutVideo($input_video, $input_banner, $output_video, $row_id = 0) {
        FileService::removeIfFileExists($output_video);
        $command = 'ffmpeg -i '. $input_video .' -i '. $input_banner .' -filter_complex "overlay=(W-w)/2:(H-h)/2" ' . $output_video . ' 2> ' .  self::commandOutputFile('addBannerOnCutVideo.txt', $row_id);
        CommonService::shellExe($command);
    }

    public static function muteStartBannerVideoPart($input_video, $output_video, $duration_in_second, $row_id = 0) {
        FileService::removeIfFileExists($output_video);
        $command = 'ffmpeg -i '. $input_video .' -af "volume=enable=\'between(t,0,' . $duration_in_second . ')\':volume=0" ' . $output_video . ' 2> ' .  self::commandOutputFile('muteStartBannerVideo.txt', $row_id);
        CommonService::shellExe($command);
    }

    public static function getTotalVideoDurationInSecond($input_video) {
        $total_duration_in_second = 0;
        try {
            $total_duration_cmd = 'ffprobe -i '. $input_video .' -show_entries format=duration -v quiet -of csv="p=0"';
            $total_duration_in_second = shell_exec($total_duration_cmd);
            $total_duration_in_second = floatval($total_duration_in_second);
        }
        catch (\Exception $exception) {

        }
        return $total_duration_in_second;
    }

    public static function getFrameRate($video_path) {
        $frame_rate = 23.98;
        try {
            $frame_rate_command = 'ffprobe -v 0 -of compact=p=0 -select_streams 0 -show_entries stream=r_frame_rate '. $video_path;
            $command_response = shell_exec($frame_rate_command);
            $r_frame_rate = str_replace("r_frame_rate=", "", $command_response);
            $r_frame_rate_parts = explode('/', $r_frame_rate);
            if (!empty($r_frame_rate_parts) && count($r_frame_rate_parts) == 2) {
                $frame_rate = intval($r_frame_rate_parts[0]) / intval($r_frame_rate_parts[1]);
                $frame_rate = number_format($frame_rate, 2, '.', '');
            }
        }
        catch (\Exception $exception) {

        }
        return $frame_rate;
    }

    protected static function writeIntoVideoTxt($video_txt_file_path, array $video_paths) {
        try {
            FileService::removeIfFileExists($video_txt_file_path);
            $video_txt = fopen($video_txt_file_path, "w") or die("Unable to open file!");
            if (!empty($video_paths)) {
                foreach ($video_paths as $video_path) {
                    $txt = "file '" . $video_path . "'\n";
                    fwrite($video_txt, $txt);
                }
            }
            fclose($video_txt);
            return $video_txt_file_path;
        }
        catch (\Exception $e) {

        }
        return '';
    }
}
