# whmcsPortForward

whmcs交流群毛子哥修改的。

[TG交流群](https://t.me/whmcsCN)


安装后端
```
bash <(curl -Ls https://mirror.ghproxy.com/https://raw.githubusercontent.com/xieruan/whmcsPortForward/main/installx.sh)
```
ehco隧道入口
```
bash <(curl -Ls https://mirror.ghproxy.com/https://raw.githubusercontent.com/xieruan/whmcsPortForward/main/installc.sh)
```
crontab -e
```
*/1 * * * *  php /usr/local/PortForward/slavec/Port_Checker.php
```
