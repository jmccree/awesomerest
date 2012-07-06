<?php
$file_config = (isset($argv[1])) ? $argv[1] : 'sshawesome.ini';
$ini_config = parse_ini_file($file_config,true);
if(!is_array($ini_config) || !isset($ini_config['sshawesome'])) {
    die("Invalid config ini file: $file_config \n");
}

$config = $ini_config['sshawesome'];
$config = str_replace('~',$_SERVER['HOME'],$config);

$e=array();
if(!is_writable($config['sshconfig'])){$e[]='SSH Config File Unwritable';}
if(strpos($config['pattern']!==0,'#')){$e[]='Invalid pattern';}
if(!is_dir($config['keydir']) || !is_readable($config['keydir'])){$e[]='Cannot access key dir';}
if(!is_array($ini_config['serverfiles']) || empty($ini_config['serverfiles'])){$e[]='No serverfiles in config';}
if(!empty($e)){die(implode("\n",$e)."\n");}else{unset($e);}

$servergroups = array();

foreach($ini_config['serverfiles'] as $serverfile) {
    $ini_serverfile = parse_ini_file($serverfile,true);
    $servergroups=array_merge_recursive($servergroups,$ini_serverfile);
}
if(empty($servergroups)){die("No server groups found\n");}

$prefix_char = (isset($config['prefix'])) ? $config['prefix'] : '-';
$ssh_servers=array();

foreach($servergroups as $group_name=>$group_servers) {
    $prefix = $group_name.$prefix_char;
    foreach($group_servers as $server_name=>$server_config) {
        $ssh=array();
	$server_config=explode(';',$server_config);
	$server_name=$prefix.$server_name;

        $connect=array();
	$s='/([^@]+)@([^:]+):?([0-9]+)?/';
	preg_match($s,$server_config[0],$connect);
	
	$ssh['Host'] = $server_name;
	$ssh['User'] = $connect[1];
	$ssh['Hostname'] = $connect[2];
	if(isset($connect[3])){$ssh['Port'] = $connect[3];}

	$keyname=$server_config[1];
	$ssh['IdentityFile'] = $config['keydir'].$keyname;

	$options = (isset($server_config[2])) ? $server_config[2] : false;
	if($options) {
	    $opt=array();parse_str($options,$opt);
	    foreach($opt as $k=>$v) {
	        $ssh[$k] = $v;
	    }
	}
        
        $ssh_servers[$server_name] = $ssh;
	unset($ssh);
    }
}
if(isset($config['debug'])){var_dump($ssh_servers);}

$pattern=$config['pattern'];
$sshawesome=$pattern."\n";
foreach($ssh_servers as $s=>$ssh_config){
    foreach($ssh_config as $c_name=>$c_val){
	$sshawesome .= "$c_name $c_val\n";
    }
}
$sshawesome.=$pattern;
if(isset($config['debug'])){echo $sshawesome."\n";}

$sshconf = file_get_contents($config['sshconfig']);
$search="/{$pattern}.*{$pattern}/s";

$newconf = preg_replace($search,$sshawesome,$sshconf);
rename($config['sshconfig'],$config['sshconfig'].time().'.bak');
file_put_contents($config['sshconfig'],$newconf);
echo "WINNING!\n";
