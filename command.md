### Mute video specific multiple duration
ffmpeg -i input_video.mp4 -af "volume=enable='between(t,0,5)':volume=0, volume=enable='between(t,45,53)':volume=0" output_video.mp4

### Total duration in second float value
ffprobe -i input_video.mp4 -show_entries format=duration -v quiet -of csv="p=0"

### Fade in fade out
`ffmpeg -i input_video.mp4 -vf "fade=t=in:st=0:d=4,fade=t=out:st=49:d=4" -c:a copy output_video.mp4`

`ffmpeg -i input_video.mp4 -vf "fade=t=in:st=0:d=4,fade=t=out:st=49:d=4" output_video.mp4`

### Mute and fade in fade out
`ffmpeg -i input_video.mp4 -af "volume=enable='between(t,0,2)':volume=0, volume=enable='between(t,50,53)':volume=0" -vf "fade=t=in:st=0:d=4,fade=t=out:st=49:d=4"  output_video.mp4`

### concat video
`ffmpeg -f concat -safe 0 -i video.txt -vf "fps=23.98"  concate.mp4`