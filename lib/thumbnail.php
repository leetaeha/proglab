<?php

/*

////////////////// 파일 업로드부분에 사용할 소스 //////////////////////

$remakeThumb = $_GET["remakeThumb"];
$filename = $file;
$ext = substr(strrchr($filename,"."),1);	//확장자앞 .을 제거하기 위하여 substr()함수를 이용
$ext = strtolower($ext);			//확장자를 소문자로 변환

// 2.사진인 경우만 이미지 직접 보여줌
if($ext == "jpg" || $ext == "gif" || $ext == "png")
{
	// 섬네일이 존재하는지 확인
	//  1. 섬네일 폴더 유무부터 확인(존재하지 않으면 생성)
	//  2. 섬네일 존재 유무 확인

	//  1.썸네일디렉토리 확인
	if(!file_exists($dir_path.THUMBNAIL_DIRECTORY))
	{
		mkdir($dir_path.THUMBNAIL_DIRECTORY);
	}

	//  2.썸네일파일확인 후 없으면 생성 or 썸네일재생성여부가 Y로 들어오면..
	if(!file_exists($dir_path.THUMBNAIL_DIRECTORY."/".$file) || $remakeThumb == "Y")
	{
		// Create_Thumbnail(썸네일을 만들 파일의 경로, 썸네일파일명, 리샘플여부) : 리샘플여부를 true로 주면 단순 resize가 아닌 resampling을 시도
		Create_Thumbnail($dir_path, $file, false);
	}

	// 원본파일 이미지 사이즈 확인
	$imgInfoArray = getimagesize($dir_path.$file);

	echo "<img src=\"".$dir_path.THUMBNAIL_DIRECTORY."/".$file."\" border=\"0\" style=\"cursor:pointer\" onClick=\"javascript:showPopUpImage('".$dir_path.$file."', ".$imgInfoArray[0].",".$imgInfoArray[1].")\"><br/>"; //이미지 보여주는 코드
	echo "{$file}</a>";

	// 이미지 리스트 "ㅵ"는 구분자(파일경로, 가로사이즈, 세로사이즈)
	$imgSrcArray[$imgcnt] = $dir_path.$file."ㅵ".$imgInfoArray[0]."ㅵ".$imgInfoArray[1];

	$imgcnt++;
}
*/


function Create_Thumbnail($pathNm, $fileNm, $resampleYn) {
	$ext = substr(strrchr($fileNm,"."),1);	//확장자앞 .을 제거하기 위하여 substr()함수를 이용
	$ext = strtolower($ext);			//확장자를 소문자로 변환

	//jpg, png, gif 인경우만
	if($ext == "jpg" || $ext == "png" || $ext == "gif")
	{
		$file = $pathNm.$fileNm;
		$imgInfoArray = getimagesize($file);
						
		$imgWidth = $imgInfoArray[0];
		$imgHeight = $imgInfoArray[1];
		$widthHeightRatio = $imgHeight / $imgWidth;

		// 가로가 긴 경우
		if($imgWidth > $imgHeight)
		{
			$imgWidth = THUMBNAIL_SIZE;
			$imgHeight = floor($imgWidth * $widthHeightRatio);
		}
		else // 세로가 길거나 같은 경우
		{
			$imgHeight = THUMBNAIL_SIZE;
			$imgWidth = floor($imgHeight / $widthHeightRatio);
		}
		
		// jpg, png, gif에 따라 코드가 다름
		if($ext == "jpg") {
			$src = imagecreatefromjpeg($file);
		}
		else if($ext == "png") {
			$src = imagecreatefrompng($file);
		} 
		else if($ext == "gif") {
			$src = imagecreatefromgif($file);
		} 

		$dst = imagecreatetruecolor($imgWidth, $imgHeight);

		if($resampleYn) {
			imagecopyresampled($dst, $src, 0, 0, 0, 0, $imgWidth, $imgHeight, $imgInfoArray[0], $imgInfoArray[1]);
		}
		else {
			imagecopyresized($dst, $src, 0, 0, 0, 0, $imgWidth, $imgHeight, $imgInfoArray[0], $imgInfoArray[1]);
		}

		// jpg, png, gif에 따라 코드가 다름
		if($ext == "jpg") {
			imagejpeg($dst, $pathNm.THUMBNAIL_DIRECTORY."/".$fileNm, THUMBNAIL_QUALITY);
		}
		else if($ext == "png") {
			imagepng($dst, $pathNm.THUMBNAIL_DIRECTORY."/".$fileNm, THUMBNAIL_QUALITY);
		} 
		else if($ext == "gif") {
			imagegif($dst, $pathNm.THUMBNAIL_DIRECTORY."/".$fileNm, THUMBNAIL_QUALITY);
		}

		ImageDestroy($dst);
		ImageDestroy($src);
	}
}
?>