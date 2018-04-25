<?php
$i = 0;
$data = [];
while(!feof(STDIN))
{
 	$line = fgets(STDIN);
	if(strpos($line, 'UNKNOWN') !== false)
	{
		$data[] = explode("	", $line);
		//print_r($data);
		$i = 1;
	}
	elseif($i == 1 && strpos($line, 'iso.') !== false)
	{
		$data[] = explode("	", $line);
		$i = 2;
	}

	if($i == 2 && count($data) == 2)
        {
                //print_r($data);
                $i = 0;
		parseData($data);
                $data = []; 
        }
//echo "i = $i || count = ". count($data) ."\n";
}

function parseData($data)
{
	$device;
	if(preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $data[0][0], $ip_match))
	{
		$hostname = getSwitchHostname($ip_match[0]); 
		echo "Sendt By: " . $ip_match[0] . " Hostname: $hostname \n";
		$device = insertDeviceToDb($ip_match[0], $hostname);
	}
	$oid = getTrapOID($data);
	$pos = getTraptype($oid);
	if(!empty($pos))
	{
		$dbdata = getTrapDataByPos($data, $pos);
		$dbdata['generic'] = parseSnmpString($dbdata['generic']);
		$dbdata['specific'] = parseSnmpString($dbdata['specific']);
		insertTrapToDb($oid, $device, $dbdata);
	}
	foreach($data[1] as $key => $value)
	{
		echo $value . "\n";
	}
}

function getTrapDataByPos($data, $pos)
{
	$pos['generic'] = $data[1][$pos['generic']];
	$pos['specific'] = $data[1][$pos['specific']];

	return $pos;
}

function getSwitchHostname($ip)
{
	$switchHostName = snmp3_get($ip, 'admin', 'authPriv', 'SHA', 'cisco123', 'DES', 'cisco123', 'iso.3.6.1.2.1.1.5.0');
	preg_match('/"(.*)"/', $switchHostName, $matches);
	$ex = explode(".", $matches[1]);
	return $ex[0];
}

function parseSnmpString($string)
{
	preg_match('/"(.*)"/', $string, $matches);
	return str_replace('"', '', $matches)[0];
}

function insertTrapToDb($oid, $device, $dbdata)
{
	echo "Inserting to db \n";
	$con = mysqli_connect("localhost", "system", "Passw0rd", "lalandia");
	mysqli_query($con, "INSERT INTO traps (device_id, oid, generic, traps.specific, traps.timestamp, created_at, updated_at) VALUES ('$device[id]', '$oid', '$dbdata[generic]', '$dbdata[specific]', NOW(), NOW(), NOW())");
}

function insertDeviceToDb($ip, $hostname)
{
    $con = mysqli_connect("localhost", "system", "Passw0rd", "lalandia");
    if(!$con)
    {
        echo "connection failed";
    }
    $result = mysqli_query($con, "SELECT * FROM devices where ip_address = '$ip' LIMIT 1");
    $device = mysqli_fetch_array($result);
    if(count($device) <= 0)
    {
        mysqli_query($con, "INSERT INTO devices (ip_address, hostname, created_at, updated_at) VALUES ('$ip','$hostname', NOW(), NOW())");
	$result = mysqli_query($con, "SELECT * FROM devices where ip_address = '$ip' LIMIT 1");
    	$device = mysqli_fetch_array($result);
	return $device;
    }
    else
    {
        echo "Device with hostname $hostname $ip already exists \n";
        return $device;
    }
}

function getTrapOID($data)
{
	$oid = $data[1][1];
	$explodedString = explode(' ', $oid);
	return $explodedString[3];
}

function getTraptype($trapOID)
{
	switch($trapOID)
	{
		case "iso.3.6.1.4.1.9.9.41.2.0.1":
			$pos['generic'] = 2;
			$pos['specific'] = 5; 
			return $pos;
	}
}

echo "Php ended";
?>
