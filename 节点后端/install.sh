#!/usr/bin/env bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH

# By Jiuling.

Green_font_prefix="\033[32m" && Red_font_prefix="\033[31m" && Green_background_prefix="\033[42;37m" && Red_background_prefix="\033[41;37m" && Font_color_suffix="\033[0m"
Info="${Green_font_prefix}[Message]${Font_color_suffix}"
Error="${Red_font_prefix}[ERROR]${Font_color_suffix}"
Tip="${Green_font_prefix}[Tip]${Font_color_suffix}"
Ver="1.2"


check_sys(){
	if [[ -f /etc/redhat-release ]]; then
		release="centos"
	elif cat /etc/issue | grep -q -E -i "debian"; then
		release="debian"
	elif cat /etc/issue | grep -q -E -i "ubuntu"; then
		release="ubuntu"
	elif cat /etc/issue | grep -q -E -i "centos|red hat|redhat"; then
		release="centos"
	elif cat /proc/version | grep -q -E -i "debian"; then
		release="debian"
	elif cat /proc/version | grep -q -E -i "ubuntu"; then
		release="ubuntu"
	elif cat /proc/version | grep -q -E -i "centos|red hat|redhat"; then
		release="centos"
    fi

	bit=`uname -m`
}

Install() {
	if [[ ${release} == "centos" ]]; then
	    yum install wget -y 
		cat /etc/redhat-release |grep 7\..*|grep -i centos>/dev/null
		if [[ $? = 1 ]]; then
			echo -e " ${Error} 不支持Centos6/8，请更换Centos 7 x64" && exit 1 
		fi
		[[ ! -e slave.zip ]] && echo -e "${Error} 被控主程序不存在，请检查 !" && exit 1
		echo -e " ${Tip} 正在安装php7.0..."
		rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
		rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
		yum install -y wget php70w.x86_64 php70w-cli.x86_64 php70w-common.x86_64 php70w-gd.x86_64 php70w-ldap.x86_64 php70w-mbstring.x86_64 php70w-mcrypt.x86_64 php70w-mysql.x86_64 php70w-pdo.x86_64
		echo -e " ${Tip} 正在安装git unzip..."
		yum install git unzip -y
		echo -e " ${Tip} 正在安装Brook..."
		wget https://github.com/txthinking/brook/releases/download/v20210401/brook_linux_amd64 -O /usr/bin/brook && chmod +x /usr/bin/brook
		echo -e " ${Tip} 正在安装Gost..."
		wget https://github.com/ginuerzh/gost/releases/download/v2.11.1/gost-linux-amd64-2.11.1.gz && gunzip gost-linux-amd64-2.11.1.gz && mv -f gost-linux-amd64-2.11.1 /usr/bin/gost && rm -f gost* && chmod +x /usr/bin/gost
		echo -e " ${Tip} 正在安装tinyPortMapper..."
		wget https://github.com/wangyu-/tinyPortMapper/releases/download/20200818.0/tinymapper_binaries.tar.gz && tar -xzf tinymapper_binaries.tar.gz && mv -f /root/tinymapper_amd64 /usr/bin/tinymapper && rm -f tinymapper* && chmod +x /usr/bin/tinymapper
		echo -e " ${Tip} 正在安装goproxy..."
		wget https://github.com/snail007/goproxy/releases/download/v10.5/proxy-linux-amd64.tar.gz && tar -xzf proxy-linux-amd64.tar.gz proxy && mv -f /root/proxy /usr/bin/goproxy  && rm -f proxy-linux* && chmod +x /usr/bin/goproxy
		echo -e " ${Tip} 禁用Firewalld..."
		service firewalld stop
		systemctl disable firewalld
		echo -e " ${Tip} 安装主程序..."
		mkdir /usr/local/PortForward
		mv slave.zip /usr/local/PortForward/
		cd /usr/local/PortForward/
		unzip slave.zip
		chmod +x -R slave
		echo -e " ${Tip} 安装完成，添加systemd守护..."
		mv /usr/local/PortForward/slave/port_forward.sh /usr/local/bin/port_forward.sh
		mv /usr/local/PortForward/slave/port_forward.service /etc/systemd/system/port_forward.service
		systemctl daemon-reload
		systemctl enable port_forward
		echo net.ipv4.ip_forward = 1 >> /etc/sysctl.conf
		sysctl -p
		echo -e " ${Tip} All Done" 
		echo -e " ${Tip} 如果这是一个新节点，请编辑 /usr/local/PortForward/slave/config.php，"
		echo -e " ${Tip} 然后手动运行一次 php /usr/local/PortForward/slave/Port_Checker.php，"
		echo -e " ${Tip} 运行完后检查config.php中的token是否与数据库对应 然后重启此被控服务器，"
		echo -e " ${Tip} 然后重启此被控服务器"

		echo -e " ${Tip} 如果这是一个重装的的节点，请编辑 /usr/local/PortForward/slave/config.php，"
		echo -e " ${Tip} 将token设置为之前的token和并编辑其他信息，"
		echo -e " ${Tip} 然后手动运行一次 php /usr/local/PortForward/slave/Port_Checker.php,"
		echo -e " ${Tip} 然后重启此被控服务器" && exit
	fi
	echo -e " ${Error} 暂不支持，敬请期待。"
}

Menu(){
check_sys
[[ ${release} != "debian" ]] && [[ ${release} != "ubuntu" ]] && [[ ${release} != "centos" ]] && echo -e "${Error} 本脚本不支持当前系统 ${release} !" && exit 1
	echo && echo -e " PortForward 被控 安装程序 ${Red_font_prefix}[v${Ver}]${Font_color_suffix}
  -- By Jiuling --
 ${Green_font_prefix}1.${Font_color_suffix} 安装程序及依赖
" && echo
if [[ -e  /usr/local/PortForward/slave/init.php ]]; then
	echo -e " 当前状态: 被控 ${Green_font_prefix}已安装${Font_color_suffix}"
else
	echo -e " 当前状态: 被控 ${Red_font_prefix}未安装${Font_color_suffix}"
fi
echo
stty erase '^H' && read -p " 请输入数字 [1-6]:" numc
case "$numc" in
	1)
	Install
	;;
	*)
	echo "请输入正确数字 [1-3]"
	;;
esac
}

Menu