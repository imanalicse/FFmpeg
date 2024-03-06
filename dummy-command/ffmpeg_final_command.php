Write text on banner:
ffmpeg -i C:/xampp/htdocs/rgs-videoapp/public/banner/latrobe/video_banner_image.png -vf "drawtext=fontfile= /Windows/fonts/calibri.ttf:fontsize=60:fontcolor=black:x=(w-text_w)/2:y=((h-text_h)/2 + 80):text=Iman Ali" image_processed.png


Name on video:
ffmpeg -i C:/xampp/htdocs/rgs-videoapp/public/banner/name_text_bg.png -vf "drawtext=fontfile=/Windows/fonts/calibri.ttf:text=Iman Ali:fontsize=35:fontcolor=black:x=(w-text_w)/2:y=((h-text_h)/2)" name_image.png

Bold
ffmpeg -i C:/xampp/htdocs/rgs-videoapp/public/banner/name_text_bg.png -vf "drawtext=fontfile=/Windows/fonts/calibri.ttf:text=Iman Ali:fontsize=35:fontcolor=black:x=(w-text_w)/2:y=((h-text_h)/2), bold=2" name_image_bold.png

ffmpeg -i C:/xampp/htdocs/rgs-videoapp/public/banner/name_text_bg.png -vf "drawtext=text='Hello World':fontcolor=white:fontsize=24:x=10:y=10, bold=2" name_image_bold.png

ffmpeg -i input.mp4 -vf "drawtext=fontfile=/path/to/font/Bold.ttf:text='Hello World':fontcolor=white:fontsize=24:x=10:y=10" output.mp4



Multiline:
ffmpeg -i name_text_bg_multiline.png -vf "drawtext=fontfile=/Windows/fonts/calibri.ttf:text=Iman Ali:fontsize=35:fontcolor=black:x=(w-text_w)/2:y=(((h-text_h)/2)-20),
drawtext=fontfile=/Windows/fonts/calibri.ttf:text=Ishak Ahmed:fontsize=35:fontcolor=black:x=(w-text_w)/2:y=(((h-text_h)/2)+20)" name_image_multiline.png



ffmpeg -i test_in.avi -vf "[in]drawtext=fontsize=20:fontcolor=White:fontfile='/Windows/Fonts/arial.ttf':text='onLine1':x=(w)/2:y=(h)/2, drawtext=fontsize=20:fontcolor=White:fontfile='/Windows/Fonts/arial.ttf':text='onLine2':x=(w)/2:y=((h)/2)+25, drawtext=fontsize=20:fontcolor=White:fontfile='/Windows/Fonts/arial.ttf':text='onLine3':x=(w)/2:y=((h)/2)+50[out]" -y test_out.avi