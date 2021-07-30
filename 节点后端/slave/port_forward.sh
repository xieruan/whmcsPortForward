sleep 60
/usr/sbin/iptables -w -F
/usr/sbin/iptables -w -F FORWARD
/usr/sbin/iptables -w -F -t nat
/usr/sbin/iptables -w -X
/usr/sbin/iptables -w -X -t nat
nic=`grep nic /usr/local/PortForward/slave/config.php | tr "'" ' ' | awk '{print $3}'`
node_bw_max=`grep node_bw_max /usr/local/PortForward/slave/config.php | tr "'" ' ' | awk '{print $3}'`
/usr/sbin/tc qdisc del dev $nic root 2>/dev/null
/usr/sbin/tc qdisc add dev $nic root handle 1: htb 2>/dev/null
/usr/sbin/tc class add dev $nic parent 1:0 classid 1:1 htb rate "$node_bw_max"Mbit 2>/dev/null
while :
do
  i=1
  while(( i <= 30 ))
  do
    php /usr/local/PortForward/slave/Traffic_Checker.php
    php /usr/local/PortForward/slave/Port_Checker.php
    let i++
    sleep 60
  done
done
