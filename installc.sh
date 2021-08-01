#!/usr/bin/env bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH
echo -e " ${Tip} 正在安装ehco..."
wget https://mirror.ghproxy.com/https://github.com/Ehco1996/ehco/releases/download/v1.0.7/ehco_1.0.7_linux_amd64 -O /usr/bin/ehco && chmod +x /usr/bin/ehco
clientConfig() {
    echo "ehco批量ws隧道入口脚本"
    echo "########ehco config#######"
    read -r -p "输入最小端口数: " pmm
    read -r -p "输入最大端口数: " pmx
    read -r -p "输入出口ip: " pp
  }

makeConfig() {
    cat >/root/client.c <<EOF
    for ((i=${pmm}; i<=${pmx}; i++))
    do
      ehco -l 0.0.0.0:$i -r ws://${pp}:$i -tt ws > /dev/null 2>&1&
    done
  EOF
  chmod +x /root/client.c
  }
echo -e " ${Tip}批量加端口中......"
./client.c
echo -e " ${Tip} 完毕退出"
done
