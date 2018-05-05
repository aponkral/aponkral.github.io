#!/bin/bash
read -r -p "This will install SoftEther to your server. Are you sure you want to continue? [y/N] " response
case $response in
[yY][eE][sS]|[yY])
echo "Updating the system first..."
apt-get update
apt-get upgrade
apt-get install checkinstall build-essential
echo "Downloading last stable release: 4.27"
sleep 2
wget http://softether-download.com/files/softether/v4.27-9666-beta-2018.04.21-tree/Linux/SoftEther_VPN_Server/64bit_-_Intel_x64_or_AMD64/softether-vpnserver-v4.27-9666-beta-2018.04.21-linux-x64-64bit.tar.gz
tar -xzf softether-vpnserver-v4.27-9666-beta-2018.04.21-linux-x64-64bit.tar.gz
cd vpnserver
echo "Please press 1 for all the following questions."
sleep 1
make
cd ..
mv vpnserver /usr/local/
chmod 600 * /usr/local/vpnserver
chmod 700 /usr/local/vpnserver/vpncmd
chmod 700 /usr/local/vpnserver/vpnserver
echo '#!/bin/sh
# description: SoftEther VPN Server
### BEGIN INIT INFO
# Provides:          vpnserver
# Required-Start:    $local_fs $network
# Required-Stop:     $local_fs
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: softether vpnserver
# Description:       softether vpnserver daemon
### END INIT INFO
DAEMON=/usr/local/vpnserver/vpnserver
LOCK=/var/lock/subsys/vpnserver
test -x $DAEMON || exit 0
case "$1" in
start)
$DAEMON start
touch $LOCK
;;
stop)
$DAEMON stop
rm $LOCK
;;
restart)
$DAEMON stop
sleep 3
$DAEMON start
;;
*)
echo "Usage: $0 {start|stop|restart}"
exit 1
esac
exit 0' > /etc/init.d/vpnserver
echo "System daemon created. Registering changes..."
sleep 2
chmod 755 /etc/init.d/vpnserver
update-rc.d vpnserver defaults
echo "SoftEther VPN Server should now start as a system service from now on. Starting SoftEther VPN service..."
../etc/init.d/vpnserver start
esac
