<?php
// 1. 연결 : mysql_connect(호스트명, 아이디, 비밀번호)
$conn=mysql_connect('27.102.204.219', 'outproglab', 'proglabout'); //db 연결부분

if($conn){
	echo "<script>console.log('db_접속 성공')</script>";
}else{
	echo "db 0 <br>";
}

// DB 선택 : mysql_select_db(해당 db명, $conn)
$db=mysql_select_db("alm", $conn);
if($db){
	echo "<script>console.log('테이블 접속 완료')</script>";
}else{
	echo "<script>console.log('테이블 접속 에러')</script>";
}
?>