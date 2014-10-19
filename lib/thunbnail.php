<?php

/*제작자 : 유창화
사용제한 : 사용은 자유롭습니다. 단, 강의나 책의 내용으로서 사용될 경우 허락을 받으셔야 합니다.*/

//모든 에러를 출력하도록 설정한다.
error_reporting(E_ALL);

//기본 함수 모음을 인클루드
include_once 'source/Yfunction.php';

//처리시간 측정시작
$Ystarttime = Yget_microtime();


//썸네일 이미지 사이지 결정하여 썸네일 생성
//$src_file_size, $dest_file_size 이미지 정보를 담은 배열 0은 너비 1은 높이
function Ysumnail_rule($src_file, $dest_file, $src_file_size, $dest_file_size, $image_quality=90, $lib='gd'){

	//생성도중 에러가 날수 있는 것들을 체크 하여 return false
	if (!is_array($src_file_size) || !is_array($dest_file_size) || empty($src_file_size[0]) || empty($src_file_size[1]) || empty($dest_file_size[0]) || empty($dest_file_size[0])) {
		return false;
	}

	//$lib 확인후 조건에 맞지 않으면 기본값 세팅
	if (empty($lib) || ($lib != 'gd' && $lib != 'netpbm')) $lib = 'gd';

	$rate = $src_file_size[1] / $src_file_size[0];
	$temp[1] = (int)($dest_file_size[0] * $rate);

	if ($dest_file_size[1] < $temp[1]) {
		$rate = $src_file_size[0] / $src_file_size[1];
		$dest_file_size[0] = (int)($dest_file_size[1] * $rate);
	}else{
		$dest_file_size[1] = $temp[1];
	}

	//썸네일의 너비나 높이가 10 미만인것은 만들지 않는다.
	if ($dest_file_size[0] < 10 || $dest_file_size[1] < 10) {
		return false;
	}

	//썸네일 이미지가 원본이미지 크기보다 크게 설정되었을 경우, 원본이미지와 동일하게
	if ($dest_file_size[0] > $src_file_size[0]) {
		$dest_file_size = $src_file_size;
	}

	if ($lib == 'netpbm'){
		return Ymake_sumnail_netpbm($src_file, $dest_file, $src_file_size, $dest_file_size, $image_quality);
	}else{
		return Ymake_sumnail_gd($src_file, $dest_file, $src_file_size, $dest_file_size, $image_quality);
	}
}

//섬네일 생성
function Ymake_sumnail_netpbm($src_file, $dest_file, $src_file_size, $dest_file_size, $image_quality=90){
	
	//생성도중 에러가 날수 있는 것들을 체크 하여 return false
	if (empty($src_file) || empty($dest_file) || !is_file($src_file) || !is_array($src_file_size) || !is_array($dest_file_size) || empty($src_file_size[0]) || empty($src_file_size[1]) || empty($src_file_size[2]) || empty($dest_file_size[0]) || empty($dest_file_size[0])) {
		return false;
	}
	
	//$image_quality 확인후 조건에 맞지 않으면 기본값 세팅
	if(!is_numeric($image_quality) || empty($image_quality)) $image_quality = 90;
	
	//원본사이즈보다 썸네일 사이즈가 더 크면 원본사이즈와 같게 썸네일을 생성
	if ($dest_file_size[0] > $src_file_size[0]) {
		$dest_file_size = $src_file_size;
	}
	
	$temp_pnm = $src_file . '.pnm';
	switch($src_file_size[2]) {
		case 1: // GIF image
			@exec( "gifsicle -I " . $src_file, $tempinfo);
			$info = @join(" " , $tempinfo);
			
			//gifsicle rpm이 설치되었을 경우 움직인는 gif도 썸네일 가능하다.
			if (preg_match("'(loop forever|loop count)'", $info)) {
				@exec( "gifsicle -O " . $image_quality . " --resize " . $dest_file_size[0] . "x" . $dest_file_size[1] . " " . $src_file . " > " . $dest_file );
				
				//퍼미션 변경가능 여부를 가지고 썸네일 생성 실패 판단
				return @chmod($dest_file, 0777);
			}else{
				@exec( "giftopnm " . $src_file . " > " . $temp_pnm);
			}
			break;
		
		case 2: // JPEG image
			@exec( "jpegtopnm " . $src_file . " > " . $temp_pnm);
			break;
		
		case 3: // PNG image
			@exec( "pngtopnm " . $src_file . " > " . $temp_pnm);
			break;
		
		case 6: // BMP image
			@exec( "bmptopnm " . $src_file . " > " . $temp_pnm);
			break;
		
		default: // 정해진 이외의 포맷은 return false
			return false;
	}
	
	@exec( "pnmscale -xy " . $dest_file_size[0] . " " . $dest_file_size[1] . " " . $temp_pnm . " | cjpeg -progressive -optimize -smooth 5 -quality " . $image_quality . " -outfile " . $dest_file);
	
	@unlink($temp_pnm);
	
	//퍼미션 변경가능 여부를 가지고 썸네일 생성 실패 판단
	return @chmod($dest_file, 0777);
}

$src_file = './temp/test.png';
$src_file_size = getimagesize($src_file);
$dest_file = './temp/s_netpbm_test.png';
$dest_file_size = Array(200, 100);
$result = Ysumnail_rule($src_file, $dest_file, $src_file_size, $dest_file_size, 90, 'netpbm');

echo "<br>png 원본이미지 <img src='" . $src_file . "' border=0>";

if (empty($result)) echo "<br>png 썸네일 생성에 실패하였습니다. 저장디렉토리의 퍼미션이나 원본의 이미지포맷, 또는 netpbm지원여부, 또는 exec 함수의 지원여부를 확인하세요.";
else echo "<br>png 썸네일이미지 <img src='" . $dest_file . "' border=0>";



$src_file = './temp/test.jpg';
$src_file_size = getimagesize($src_file);
$dest_file = './temp/s_netpbm_test.jpg';
$dest_file_size = Array(200, 100);
$result = Ysumnail_rule($src_file, $dest_file, $src_file_size, $dest_file_size, 90, 'netpbm');

echo "<br>jpg 원본이미지 <img src='" . $src_file . "' border=0>";

if (empty($result)) echo "<br><b>jpg 썸네일 생성에 실패하였습니다. 저장디렉토리의 퍼미션이나 원본의 이미지포맷, 또는 netpbm지원여부, 또는 exec 함수의 지원여부를 확인하세요.</b>";
else echo "<br>jpg 썸네일이미지 <img src='" . $dest_file . "' border=0>";



$src_file = './temp/test.gif';
$src_file_size = getimagesize($src_file);
$dest_file = './temp/s_netpbm_test.gif';
$dest_file_size = Array(200, 100);
$result = Ysumnail_rule($src_file, $dest_file, $src_file_size, $dest_file_size, 90, 'netpbm');

echo "<br>gif 원본이미지 <img src='" . $src_file . "' border=0>";

if (empty($result)) echo "<br><b>gif 썸네일 생성에 실패하였습니다. 저장디렉토리의 퍼미션이나 원본의 이미지포맷, 또는 netpbm지원여부, 또는 exec 함수의 지원여부를 확인하세요.</b>";
else echo "<br>gif 썸네일이미지 <img src='" . $dest_file . "' border=0>";



$src_file = './temp/test.bmp';
$src_file_size = getimagesize($src_file);
$dest_file = './temp/s_netpbm_test.bmp';
$dest_file_size = Array(200, 100);
$result = Ysumnail_rule($src_file, $dest_file, $src_file_size, $dest_file_size, 90, 'netpbm');

echo "<br>bmp 원본이미지 <img src='" . $src_file . "' border=0>";

if (empty($result)) echo "<br><b>bmp 썸네일 생성에 실패하였습니다. 저장디렉토리의 퍼미션이나 원본의 이미지포맷, 또는 netpbm지원여부, 또는 exec 함수의 지원여부를 확인하세요.</b>";
else echo "<br>bmp 썸네일이미지 <img src='" . $dest_file . "' border=0>";



$src_file = './temp/testanimation.gif';
$src_file_size = getimagesize($src_file);
$dest_file = './temp/s_netpbm_testanimation.gif';
$dest_file_size = Array(200, 100);
$result = Ysumnail_rule($src_file, $dest_file, $src_file_size, $dest_file_size, 90, 'netpbm');

echo "<br>움직이는 gif 원본이미지 <img src='" . $src_file . "' border=0>";

if (empty($result)) echo "<br><b>움직이는 gif 썸네일 생성에 실패하였습니다. 저장디렉토리의 퍼미션이나 원본의 이미지포맷, 또는 netpbm지원여부, 또는 gifsicle 지원여부, 또는 exec 함수의 지원여부를 확인하세요.</b>";
else echo "<br>움직이는 gif 썸네일이미지 <img src='" . $dest_file . "' border=0>";

?>
<?php

//처리시간 출력
Yecho_usetime($Ystarttime);

//설명글 출력
$guide_text = '
[Ysumnail_rule 요약]
썸네일을 원본의 비율대로 생성하여 줍니다.
netpbm를 사용하여 썸네일을 만듭니다.
gif, jpeg, png, bmp 포맷만 가능합니다.
썸네일의 확장자는 원본과 동일하나 파일포맷은 모두 jpeg입니다.
처리속도는 gd로 처리할때보다 조금 더 걸리나 결과물의 질이나 용량은 더 작습니다.

서버에 다음의 rpm이 설치되어야 사용가능합니다.
netpbm
netpbm-progs

움직이는 gif를 그대로 썸네일로 만들려면
http://www.lcdf.org/gifsicle/ 배포되는 rpm이 시스템에 설치되어있어야 합니다.

[리턴값]
썸네일 생성 성공시 true, 실패시 false;

[사용법] 
Ysumnail_rule(원본파일 경로, 생성할 썸네일경로, 이미지정보배열(0=>원본 너비, 1=>원본 높이, 2=>이미지포맷정보), 생성할 썸네일정보배열(0=>원본 너비, 1=>원본 높이), 썸네일생성질(100 이하의 숫자입력), 사용할라이브러리 지정(gd 나 netpbm))
';

Yecho_guide($guide_text);

//소스보기 출력
Yecho_viewsource();

?> 