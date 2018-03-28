#!/bin/sh
echo "#!/bin/sh
sudo /usr/bin/buildkernel.sh https://www.kernel.org/pub/linux/kernel/v4.x/linux-4.9.10.tar.gz > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > kernel-build
chmod +x kernel-build
