netstat=$(sudo netstat -peanut | grep snmptrapd | grep -o -E '[0-9]+')
snmptrapdpid=$(echo $netstat | sed 's/.* //')
sudo kill -9 $snmptrapdpid

sudo snmptrapd -f -Lo

