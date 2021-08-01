#!/usr/bin/env bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH
echo -e " ${Tip} 正在安装ehco..."
wget https://mirror.ghproxy.com/https://github.com/Ehco1996/ehco/releases/download/v1.0.7/ehco_1.0.7_linux_amd64 -O /usr/bin/ehco && chmod +x /usr/bin/ehco
echo -e " ${Tip} 完毕退出"
done
