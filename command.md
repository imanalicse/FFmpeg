### Mute video specific multiple duration
ffmpeg -i input_video.mp4 -af "volume=enable='between(t,0,5)':volume=0, volume=enable='between(t,45,53)':volume=0" output_video.mp4