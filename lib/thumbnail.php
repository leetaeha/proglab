<?php
/*
////////////////// 파일 업로드부분에 사용할 소스 //////////////////////

$remakeThumb = $_GET["remakeThumb"];

$filename = $remakeThumb;
$ext = substr(strrchr($filename,"."),1);	//확장자앞 .을 제거하기 위하여 substr()함수를 이용
$ext = strtolower($ext);			//확장자를 소문자로 변환

if($ext == "jpg" || $ext == "gif" || $ext == "png")
{
	// 원본파일 이미지 사이즈 확인
	$img_info = getimagesize($dir_path.$file);
	
	//$img_info[0] => 68 //가로사이즈
	//$img_info[1] => 100 //세로사이즈
	//$img_info[bits] => 8 //이미지사이즈
	
	// 원본 이미지파일의 사이즈가 일정 이상일 경우 썸네일 생성 
	if($img_info[0] > 128 || $img_info[1] > 128 || $img_info[bits] > 100)
	{
		// Create_Thumbnail(썸네일을 만들 파일의 경로, 썸네일파일명, 리샘플여부) : 리샘플여부를 true로 주면 단순 resize가 아닌 resampling을 시도
		Create_Thumbnail($dir_path, $file, false);
	}
}
*/

function Create_Thumbnail($pathNm, $fileNm, $resampleYn) {
	$ext = substr(strrchr($fileNm,"."),1);	//확장자앞 .을 제거하기 위하여 substr()함수를 이용
	$ext = strtolower($ext);			//확장자를 소문자로 변환

	//jpg, png, gif 인경우만
	if($ext == "jpg" || $ext == "png" || $ext == "gif")
	{
		$file = $pathNm.$fileNm;
		$img_infoArray = getimagesize($file);
						
		$imgWidth = $img_infoArray[0];
		$imgHeight = $img_infoArray[1];
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
			imagecopyresampled($dst, $src, 0, 0, 0, 0, $imgWidth, $imgHeight, $img_infoArray[0], $img_infoArray[1]);
		}
		else {
			imagecopyresized($dst, $src, 0, 0, 0, 0, $imgWidth, $imgHeight, $img_infoArray[0], $img_infoArray[1]);
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