#! /usr/bin/bash

sudo systemctl status ff-queue.service | head -n 5
echo
sudo systemctl status ff-schedule.service | head -n 5
echo
sudo systemctl status ff-schedule.timer | head -n 5
echo
sudo systemctl status ff-reverb.service | head -n 5
echo
