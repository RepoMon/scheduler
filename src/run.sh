#!/usr/bin/env bash

env > /root/env.txt

# Start the cron service in the background.
cron -f &

while (true)
do
    php consume.php
    sleep 1
    echo "restarting service"
done