#!/bin/sh

# объединение video и audio
function combine()
{
	ffmpeg -y -i video.mp4 -i audio.mp4 -acodec copy -vcodec copy output.mp4
}

# сборка m4s астей в единый mp4 файл
function cat_parts()
{
	TYPE=$1
	cp ./$TYPE/init.mp4 ./$TYPE.mp4
	for ((i=1; i<9999; i++))
	do
		F="./$TYPE/segment-$i.m4s"
		if [ ! -f $F ]; then break; fi
		#echo "Save $F..."
		cat $F >> ./$TYPE.mp4
	done
}

# перебираем все части
for dir in ./parts/*
do
	echo $dir
	cd $dir
	cat_parts video
	cat_parts audio
	combine
	cd $OLDPWD
done
