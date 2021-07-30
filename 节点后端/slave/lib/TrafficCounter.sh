#!/bin/bash
	ACTION=$1
	PORT=$2
	BANDWIDTH=$3
	FORWARDPORT=$4
	FORWARDIP=$5
	METHOD=$6
	NIC=$7
	NODEBANDWIDTH=$8
	BURST=$9
	IPTABLES=/usr/sbin/iptables
	TC=/usr/sbin/tc
	CHAIN_NAME_TCP="TLG-TCP-$PORT"
	CHAIN_NAME_UDP="TLG-UDP-$PORT"

	function disable(){
	    #clear up
		if [ "$METHOD" == "iptables" ]; then
		  $IPTABLES -w -D FORWARD -p tcp --sport $FORWARDPORT -s "$FORWARDIP"/32 -j $CHAIN_NAME_TCP 2>/dev/null
		  $IPTABLES -w -D FORWARD -p udp --sport $FORWARDPORT -s "$FORWARDIP"/32 -j $CHAIN_NAME_UDP 2>/dev/null
		else
	      $IPTABLES -w -D INPUT -p tcp --dport $PORT 2>/dev/null
	      $IPTABLES -w -D INPUT -p udp --dport $PORT 2>/dev/null
		  $IPTABLES -w -D OUTPUT -p tcp --sport $PORT -j $CHAIN_NAME_TCP 2>/dev/null
		  $IPTABLES -w -D OUTPUT -p udp --sport $PORT -j $CHAIN_NAME_UDP 2>/dev/null
		fi
	    $IPTABLES -w -F $CHAIN_NAME_TCP 2>/dev/null
	    $IPTABLES -w -X $CHAIN_NAME_TCP 2>/dev/null
	    $IPTABLES -w -F $CHAIN_NAME_UDP 2>/dev/null
	    $IPTABLES -w -X $CHAIN_NAME_UDP 2>/dev/null
	}

	function enable(){
	    disable
		if [ "$BURST" == "true" ]; then
		  SPEEDLIMIT=$NODEBANDWIDTH
		else
		  SPEEDLIMIT=$BANDWIDTH
		fi
		$TC class add dev $NIC parent 1:1 classid 1:"$BANDWIDTH" htb rate "$BANDWIDTH"Mbit ceil "$SPEEDLIMIT"Mbit 2>/dev/null
		$TC qdisc add dev $NIC parent 1:"$BANDWIDTH" handle "$BANDWIDTH": sfq perturb 5 2>/dev/null
		$TC filter add dev $NIC parent 1:0 protocol ip prio 1 handle $BANDWIDTH fw classid 1:"$SPEEDLIMIT" 2>/dev/null
	    $IPTABLES -w -N $CHAIN_NAME_TCP
	    $IPTABLES -w -N $CHAIN_NAME_UDP
		if [ "$METHOD" == "iptables" ]; then
			$IPTABLES -w -A FORWARD -p tcp --sport $FORWARDPORT -s "$FORWARDIP"/32 -j $CHAIN_NAME_TCP 2>/dev/null
			$IPTABLES -w -A FORWARD -p udp --sport $FORWARDPORT -s "$FORWARDIP"/32 -j $CHAIN_NAME_UDP 2>/dev/null
			$IPTABLES -w -A $CHAIN_NAME_TCP -j MARK --set-mark="$BANDWIDTH"
			$IPTABLES -w -A $CHAIN_NAME_UDP -j MARK --set-mark="$BANDWIDTH"
		else
	    	$IPTABLES -w -A INPUT -p tcp --dport $PORT 2>/dev/null
	    	$IPTABLES -w -A INPUT -p udp --dport $PORT 2>/dev/null
			$IPTABLES -w -A OUTPUT -p tcp --sport $PORT -j $CHAIN_NAME_TCP 2>/dev/null
			$IPTABLES -w -A OUTPUT -p udp --sport $PORT -j $CHAIN_NAME_UDP 2>/dev/null
			$IPTABLES -w -A $CHAIN_NAME_TCP  -j MARK --set-mark="$BANDWIDTH"
	    	$IPTABLES -w -A $CHAIN_NAME_UDP  -j MARK --set-mark="$BANDWIDTH"
		fi
	}

	function show(){
	    in_bytes=`$IPTABLES -w -L $CHAIN_NAME_TCP -Z -vnx | tail -2 | head -1 | awk '{print $2}'`
	    out_bytes=`$IPTABLES -w -L $CHAIN_NAME_UDP -Z -vnx | tail -2 | head -1 | awk '{print $2}'`
	    echo "[$PORT,$in_bytes,$out_bytes]"
	}



	case "$ACTION" in
	    enable)
	        echo "setup stats capture rule for $PORT"
	        enable
	        echo "done"
	        ;;
	    disable)
	        echo "remove stats capture rule for $PORT"
	        disable
	        echo "done"
	        ;;
	    show)
	        show
	        ;;
	esac