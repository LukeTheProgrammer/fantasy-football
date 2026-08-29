#! /usr/bin/bash

echo 'restarting queue...'
sudo systemctl restart ff-queue.service
echo 'restarting schedule...'
sudo systemctl restart ff-schedule.service
echo 'restarting timer...'
sudo systemctl restart ff-schedule.timer
echo 'restarting reverb...'
sudo systemctl restart ff-reverb.service
echo
