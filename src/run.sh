#!/usr/bin/env bash

# Start the cron service in the background.
cron -f &

while (true)
do
    php consume.php
    sleep 1
done