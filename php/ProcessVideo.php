<?php

namespace App\Console\Commands;

use App\Models\StudentStageVideo;
use App\Services\CommonService;
use App\Services\FileService;
use App\Services\UniversityService;
use App\Traits\CommonTrait;
use Illuminate\Console\Command;
use App\Services\FfmpegService;
use App\Enum\EmailType;
use App\Services\EmailService;

class ProcessVideo extends Command
{
    use CommonTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $student_stage_video = StudentStageVideo::where('is_video_processed', 0)
            ->where('is_video_processing', 0)
            ->where('video_source_url', '!=', '')
            ->orderBy('process_attempts', 'asc')
            ->orderBy('id', 'desc')
            ->first();
        if (!empty($student_stage_video)) {
            $row_id = $student_stage_video['id'];
            try {
                echo date("Y-m-d H:i:s"). " === start video_id === " . $row_id ."\n";
                info('$student_stage_video: '. $student_stage_video);
                $student_stage_video->is_video_processing = 1;
                $student_stage_video->process_attempts = $student_stage_video->process_attempts + 1;
                $student_stage_video->process_start_at  = date("Y-m-d H:i:s");
                $student_stage_video->save();

                $main_video_path = FileService::mainVideoPath() . $student_stage_video['video_source_url'];
                if (!is_file($main_video_path)) {
                    $student_stage_video->is_process_failed = 1;
                    $student_stage_video->save();
                    echo "main_video_file_missing: $row_id -> $main_video_path \n";
                    return '';
                }
                $processed_video_path = CommonService::getProcessVideoAbsPath($student_stage_video);
                // $subdomain = UniversityService::getSubdomainById($student_stage_video['university_id']);
                echo 'main_video_path: '. $main_video_path . "\n";
                echo 'process_video_path: '.$processed_video_path . "\n";

                $processed_video_dir = dirname($processed_video_path);
                if (!is_dir($processed_video_dir) && !is_file($processed_video_dir)) {
                    FileService::createFolder($processed_video_dir,'0775');
                }

                // $temp_video_directory = public_path() . DIRECTORY_SEPARATOR .'tmpVideo'. DIRECTORY_SEPARATOR. $row_id;
                $temp_video_directory = FileService::getOutSidePath() . 'uploads/' .'tmpVideo'. DIRECTORY_SEPARATOR. $row_id;
                if (!is_dir($temp_video_directory) && !is_file($temp_video_directory)) {
                    FileService::createFolder($temp_video_directory,'0775');
                }

                // $main_image_path = public_path() . DIRECTORY_SEPARATOR .'banner'. DIRECTORY_SEPARATOR . $subdomain .DIRECTORY_SEPARATOR . 'video_banner_image.png';
                $main_image_path = CommonService::getBannerAbsPath($student_stage_video);
                $name_text_bg_image = public_path() . DIRECTORY_SEPARATOR .'banner'. DIRECTORY_SEPARATOR . 'name_text_bg.png';
                $video_ext  = pathinfo($main_video_path, PATHINFO_EXTENSION);
                $processed_image = $temp_video_directory . '/image_processed_' . $row_id . '.png';
                $name_image = $temp_video_directory . '/name_image_' . $row_id . '.png';
                $image_video_path = $temp_video_directory . '/image_to_video_' . $row_id . '.' . $video_ext;
                $banner_video_path = $temp_video_directory . '/banner_video_' . $row_id . '.' . $video_ext;
                $main_video_with_name = $temp_video_directory . '/main_video_with_name_' . $row_id . '.' . $video_ext;
                $cut_video = $temp_video_directory . '/cut_video_' . $row_id . '.' . $video_ext;
                $concat_video = $temp_video_directory . '/concat_video_' . $row_id . '.' . $video_ext;
                $banner_video_duration_in_second = 2;
                $font_file = FileService::getFontFile();
                echo "font_file: " . $font_file ."\n";

                $text = $student_stage_video["video_append_text"];
                $frame_rate = FfmpegService::getFrameRate($main_video_path);
                if (is_file($main_image_path)) {
                    FfmpegService::writeTextOnImage($main_image_path, $processed_image, $font_file, $text, $row_id);
//                    FfmpegService::imageToVideo($processed_image, $image_video_path, $frame_rate, $row_id);
//                    FfmpegService::setAudioTrack($image_video_path, $banner_video_path, $row_id);

                    FfmpegService::cutVideo($main_video_path, $cut_video, $banner_video_duration_in_second, $row_id);
                    FfmpegService::addBannerOnCutVideo($cut_video, $processed_image, $banner_video_path, $row_id);
                }
                FfmpegService::generateNameImage($name_text_bg_image, $name_image, $text, $font_file, $row_id);
                if (is_file($main_image_path)) {
                    FfmpegService::addNamBannerToVideo($main_video_path, $name_image, $main_video_with_name, $row_id);
                }
                else {
                    FfmpegService::addNamBannerToVideo($main_video_path, $name_image, $processed_video_path, $row_id);
                }
                if (is_file($main_image_path)) {
                    $video_paths = [
                        $banner_video_path,
                        $main_video_with_name,
                        $banner_video_path
                    ];
                   // FfmpegService::concatVideo($video_paths, $processed_video_path, $temp_video_directory . '/video_' . $row_id . '.txt', $frame_rate, $row_id);

                    FfmpegService::concatVideo($video_paths, $concat_video, $temp_video_directory . '/video_' . $row_id . '.txt', $frame_rate, $row_id);
                    FfmpegService::muteStartBannerVideoPart($concat_video, $processed_video_path, $banner_video_duration_in_second, $row_id);
                }

                // FfmpegService::addPeriodicText($combined_video_path, $processed_video_path, $text, $font_file, $row_id);

                if (is_file($processed_video_path) && CommonService::realFileSize($processed_video_path)) {
                    $student_stage_video->processed_video_url = str_replace(FileService::getOutSidePath(), '', $processed_video_path);
                    $student_stage_video->is_video_processed = 1;
                    $student_stage_video->is_process_failed = 0;
                    $student_stage_video->processed_at  = date("Y-m-d H:i:s");
                    $student_stage_video->expired_at = CommonService::processVideoExpiredAt();
                    $student_stage_video->deleted_at = null;
                    $student_stage_video->save();

                    $s3_object_url = $this->processVideoS3ByRecord($student_stage_video);
                    echo 's3_object_url: '. $s3_object_url;
                    if (!empty($s3_object_url)) {

                    }
                    else {
                        echo " Unable to upload s3: " . $row_id ."\n";
                    }

                    $send_email_data = [
                        'university_id' => $student_stage_video['university_id'],
                        'model_id' => $row_id,
                        'model_name' => 'StudentStageVideos',
                        'order_id' => $student_stage_video['order_id'],
                        'email_type' => EmailType::VIDEO_PROCESSED,
                    ];

                    EmailService::saveSendEmail($send_email_data);

                    $this->saveNotification($row_id);
                }
                else {
                    $student_stage_video->is_process_failed = 1;
                    $student_stage_video->save();
                }
                echo date("Y-m-d H:i:s"). " end video_id: " . $row_id ."\n";
            }
            catch (\Exception $exception) {
                $student_stage_video->is_process_failed = 1;
                $student_stage_video->save();
                echo date("Y-m-d H:i:s"). " video processing error ($row_id): " . $exception->getMessage() ." \n";
            }
        }
    }
}
