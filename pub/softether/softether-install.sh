#!/bin/bash
# Sofether installation wrapper
# https://aponkral.net

#
# Currently Supported Operating Systems:
#
#   CentOS 6, Centos 7
#   Ubuntu 16.04
#

# Am I root?
if [ "x$(id -u)" != 'x0' ]; then
    echo 'Error: this script can only be executed by root'
    exit 1
fi

# Check admin user account
if [ ! -z "$(grep ^admin: /etc/passwd)" ] && [ -z "$1" ]; then
    echo "Error: user admin exists"
    echo
    echo 'Please remove admin user account before proceeding.'
    echo 'If you want to do it automatically run installer with -f option:'
    echo "Example: bash $0 --force"
    exit 1
fi

# Check admin user account
if [ ! -z "$(grep ^admin: /etc/group)" ] && [ -z "$1" ]; then
    echo "Error: group admin exists"
    echo
    echo 'Please remove admin user account before proceeding.'
    echo 'If you want to do it automatically run installer with -f option:'
    echo "Example: bash $0 --force"
    exit 1
fi

# Detect OS
case $(head -n1 /etc/issue | cut -f 1 -d ' ') in
    Debian)     type="debian" ;;
    Ubuntu)     type="ubuntu" ;;
    *)          type="centos" ;;
esac

# Check wget
if [ -e '/usr/bin/wget' ]; then
    wget https://aponkral.github.io/pub/softether/softether-install-$type.sh -O softether-install-$type.sh
    if [ "$?" -eq '0' ]; then
        chmod +x softether-install-$type.sh
        bash softether-install-$type.sh $*
        exit
    else
        echo "Error: softether-install-$type.sh download failed."
        exit 1
    fi
fi

# Check curl
if [ -e '/usr/bin/curl' ]; then
    curl -O https://aponkral.github.io/pub/softether/softether-install-$type.sh
    if [ "$?" -eq '0' ]; then
        chmod +x softether-install-$type.sh
        bash softether-install-$type.sh $*
        exit
    else
        echo "Error: softether-install-$type.sh download failed."
        exit 1
    fi
fi

exit