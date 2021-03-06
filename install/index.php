<?php

include 'auto.php';
if (IS_SAE)
	header("Location: index_sae.php");

if (file_exists('./install.lock')) {
	echo '
		<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        </head>
        <body>
        你已经安装过该系统，如果想重新安装，请先删除站点Install目录下的 install.lock 文件，然后再安装。
        </body>
        </html>';
	exit;
}
@set_time_limit(1000);

if (version_compare(phpversion(), '5.3.0', '<')) {
	header("Content-type:text/html;charset=utf-8");
	exit('您的php版本过低，不能安装本软件，请升级到5.3.0或更高版本再安装，谢谢！');
}


date_default_timezone_set('PRC');
error_reporting(E_ALL);
header('Content-Type: text/html; charset=UTF-8');
define('SITEDIR', _dir_path(substr(dirname(__FILE__), 0, -8)));
define("SIMPLEWIND_CMF_VERSION", '20131111');

//数据库
$sqlFile = 'table.sql';
$configFile = 'config.php';
if (!file_exists(SITEDIR . 'install/' . $sqlFile) || !file_exists(SITEDIR . 'install/' . $configFile)) {
	echo '缺少必要的安装文件!';
	exit;
}
$Title = "ThinkCMF";
$Powered = "Powered by www.thinkcmf.com";
$steps = array(
	'1' => '安装许可协议',
	'2' => '运行环境检测',
	'3' => '安装参数设置',
	'4' => '安装详细过程',
	'5' => '安装完成',
);
$step = isset($_GET['step']) ? $_GET['step'] : 1;

//地址
$scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
$rootpath = @preg_replace("/\/(I|i)nstall\/index\.php(.*)$/", "", $scriptName);
$domain = empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
if ((int)$_SERVER['SERVER_PORT'] != 80) {
	$domain .= ":" . $_SERVER['SERVER_PORT'];
}
$domain = $domain . $rootpath;

switch ($step) {

	case '1':
		include_once("./templates/s1.php");
		exit();

	case '2':

		if (phpversion() < 5) {
			die('本系统需要PHP5+MYSQL >=4.1环境，当前PHP版本为：' . phpversion());
		}

		$phpv = @ phpversion();
		$os = PHP_OS;
		$os = php_uname();
		$tmp = function_exists('gd_info') ? gd_info() : array();
		$server = $_SERVER["SERVER_SOFTWARE"];
		$host = (empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"]);
		$name = $_SERVER["SERVER_NAME"];
		$max_execution_time = ini_get('max_execution_time');
		$allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
		$allow_url_fopen = (ini_get('allow_url_fopen') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
		$safe_mode = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');

		$err = 0;
		if (empty($tmp['GD Version'])) {
			$gd = '<font color=red>[×]Off</font>';
			$err++;
		} else {
			$gd = '<font color=green>[√]On</font> ' . $tmp['GD Version'];
		}
		if (class_exists('PDO')) {
			$mysql = '<span class="correct_span">&radic;</span> 已安装';
		} else {
			$mysql = '<span class="correct_span error_span">&radic;</span> 出现错误';
			$err++;
		}
		if (ini_get('file_uploads')) {
			$uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
		} else {
			$uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
		}
		if (function_exists('session_start')) {
			$session = '<span class="correct_span">&radic;</span> 支持';
		} else {
			$session = '<span class="correct_span error_span">&radic;</span> 不支持';
			$err++;
		}
		$folder = array(
			'/',
			'install',
			'Application',
			'Application/Common',
			'Application/Common/Conf',
			'Application/Runtime',
			'Application/Runtime/Cache',
			'Application/Runtime/Data',
			'Application/Runtime/Logs',
			'Application/Runtime/Temp',
			'Uploads',
			'Public',
		);
		include_once("./templates/s2.php");
		exit();

	case '3':

		if (!empty($_GET['testdbpwd'])) {
			$host = empty($_POST['dbHost']) ? '127.0.0.1' : trim($_POST['dbHost']);
			$port = empty($_POST['dbPort']) ? '3306' : $_POST['dbPort'] + 0;
			$user = empty($_POST['dbUser']) ? 'root' : trim($_POST['dbUser']);
			$password = empty($_POST['dbPwd']) ? 'root' : trim($_POST['dbPwd']);
			$dsn = sprintf('mysql:host=%s;port=%s', $host, $port);
			try {
				$dbh = new \PDO($dsn, $user, $password);
				die('1');
			} catch (\Exception $e) {
				die('');
			}
		}
		include_once("./templates/s3.php");
		exit();


	case '4':
		if (empty($_GET['install'])) {
			include_once("./templates/s4.php");
			exit();
		}

		$n = isset($_GET['n']) ? $_GET['n'] + 0 : 0;
		$arr = array();

		$dbHost = empty($_POST['dbhost']) ? '127.0.0.1' : trim($_POST['dbhost']);
		$dbPort = empty($_POST['dbport']) ? '3306' : $_POST['dbport'] + 0;
		$dbName = empty($_POST['dbname']) ? 'mf' : strtolower(trim($_POST['dbname']));
		$dbUser = empty($_POST['dbuser']) ? 'root' : trim($_POST['dbuser']);
		$dbPwd = empty($_POST['dbpw']) ? 'root' : trim($_POST['dbpw']);
		$dbPrefix = empty($_POST['dbprefix']) ? 'mf_' : trim($_POST['dbprefix']);
		$username = isset($_POST['manager']) ? trim($_POST['manager']) : '';
		$password = isset($_POST['manager_pwd']) ? trim($_POST['manager_pwd']) : '';
		$email = isset($_POST['manager_email']) ? trim($_POST['manager_email']) : '';

		$dsn = sprintf('mysql:host=%s;port=%s;', $dbHost, $dbPort);
		try {
			$dbh = new \PDO($dsn, $dbUser, $dbPwd);
			$dbh->query('SET NAMES utf8');
			$sth = $dbh->prepare('SELECT version()');
			$sth->execute();
			$version = $sth->fetchColumn();
			if (version_compare($version, '4.2', '<')) {
				throw new \Exception('数据库版本太低!');
			}
			$aff = $dbh->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s` DEFAULT CHARSET utf8', $dbName));
			if (false === $aff) {
				throw new \Exception(sprintf('创建数据库%s失败', $dbName));
			}

			$dbh->query("USE $dbName");

			//读取数据文件
			$sqldata = file_get_contents(SITEDIR . 'install/' . $sqlFile);
			$sqlFormat = sql_split($sqldata, $dbPrefix);

			/**
			 * 执行SQL语句
			 */
			$counts = count($sqlFormat);

			for ($i = $n; $i < $counts; $i++) {
				$sql = $sqlFormat[$i];
				if (empty($sql)) continue;

				try {
					$ret = $dbh->exec($sql);
					if (strstr($sql, 'CREATE TABLE')) {
						preg_match('/CREATE TABLE (?:IF NOT EXISTS )?`([^ ]*)`/', $sql, $matches);
						//mysqli_query($conn, "DROP TABLE IF EXISTS `$matches[1]");

						if ($ret !== false) {
							$message = '<li><span class="correct_span">&radic;</span>创建数据表' . $matches[1] . '，完成</li>';
						} else {
							$message = '<li><span class="correct_span error_span">&radic;</span>创建数据表' . $matches[1] . '，失败</li>';
						}
					} else {
						if ($ret !== false) {
							$message = '<li><span class="correct_span">&radic;</span>新增/更新数据成功</li>';
						} else {
							$message = sprintf('<li><span class="correct_span error_span">&radic;</span>新增/更新数据失败：%s</li>', $sql);
						}
					}
				} catch (\PDOException $e) {
					$message = sprintf('<li><span class="correct_span error_span">&radic;</span>执行失败：%s</li>', $e->getMessage());
				}

				$i++;
				$arr = array('n' => $i, 'msg' => $message);
				die(json_encode($arr));
			}
			if ($i == 999999) exit;

			//读取配置文件，并替换真实配置数据
			$strConfig = file_get_contents(SITEDIR . 'install/' . $configFile);
			$strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
			$strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
			$strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
			$strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
			$strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
			$strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
			@chmod(SITEDIR . '/Application/Common/Conf/config.php', 0777);
			@file_put_contents(SITEDIR . '/Application/Common/Conf/config.php', $strConfig);

			//插入管理员
			$password = md5($password);
			$query = "REPLACE INTO `{$dbPrefix}admin`
                (admin_id,username,password,email,register_time,role_id)
                VALUES ('1', '{$username}', '{$password}', '{$email}', now(), '1')";
			$aff = $dbh->exec($query);
			if (false === $aff) {
				$message = '添加管理员失败<br />';
			} else {
				$message = '成功添加管理员<br />成功写入配置文件<br>安装完成．';
			}

			$arr = array('n' => 999999, 'msg' => $message);
			echo json_encode($arr);
			exit;
		} catch (\PDOException $e) {
			$arr['msg'] = $e->getMessage();
			die(json_encode($arr));
		} catch (\Exception $e) {
			$arr['msg'] = $e->getMessage();
			die(json_encode($arr));
		}

	case '5':
		$ip = get_client_ip();
		$host = $_SERVER['HTTP_HOST'];
		include_once("./templates/s5.php");
		@touch('./install.lock');
		exit();
}

function testwrite($d)
{
	$tfile = "_test.txt";
	$fp = @fopen($d . "/" . $tfile, "w");
	if (!$fp) {
		return false;
	}
	fclose($fp);
	$rs = @unlink($d . "/" . $tfile);
	if ($rs) {
		return true;
	}
	return false;
}


function sql_split($sql, $tablepre)
{

	if ($tablepre != "mf_")
		$sql = str_replace("mf_", $tablepre, $sql);
	$sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach ($queriesarray as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		$queries = array_filter($queries);
		foreach ($queries as $query2) {
			$str1 = substr($query2, 0, 1);
			if ($str1 != '#' && $str1 != '-')
				$ret[$num] .= $query2;
		}
		$num++;
	}
	return $ret;
}

function _dir_path($path)
{
	$path = str_replace('\\', '/', $path);
	if (substr($path, -1) != '/')
		$path = $path . '/';
	return $path;
}

// 获取客户端IP地址
function get_client_ip()
{
	static $ip = NULL;
	if ($ip !== NULL)
		return $ip;
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$pos = array_search('unknown', $arr);
		if (false !== $pos)
			unset($arr[$pos]);
		$ip = trim($arr[0]);
	} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
	return $ip;
}

function dir_create($path, $mode = 0777)
{
	if (is_dir($path))
		return TRUE;
	$path = dir_path($path);
	$temp = explode('/', $path);
	$cur_dir = '';
	$max = count($temp) - 1;
	for ($i = 0; $i < $max; $i++) {
		$cur_dir .= $temp[$i] . '/';
		if (@is_dir($cur_dir))
			continue;
		@mkdir($cur_dir, $mode, true);
		@chmod($cur_dir, $mode);
	}
	return is_dir($path);
}

function dir_path($path)
{
	$path = str_replace('\\', '/', $path);
	if (substr($path, -1) != '/')
		$path = $path . '/';
	return $path;
}

function sp_password($pw, $pre)
{
	$decor = md5($pre);
	$mi = md5($pw);
	return substr($decor, 0, 12) . $mi . substr($decor, -4, 4);
}

function sp_random_string($len = 6)
{
	$chars = array(
		"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
		"l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
		"w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
		"H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
		"S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
		"3", "4", "5", "6", "7", "8", "9"
	);
	$charsLen = count($chars) - 1;
	shuffle($chars);    // 将数组打乱
	$output = "";
	for ($i = 0; $i < $len; $i++) {
		$output .= $chars[mt_rand(0, $charsLen)];
	}
	return $output;
}

?>