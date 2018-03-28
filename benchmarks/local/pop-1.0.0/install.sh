#!/bin/sh

echo "#!/bin/sh
SLEEPTIME=10
echo \"Running for \$1 seconds.\"
sleep \$SLEEPTIME" > pop
chmod +x pop
