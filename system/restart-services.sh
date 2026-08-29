#! /usr/bin/bash

sudo systemctl restart ff-queue.service
echo
sudo systemctl restart ff-schedule.service
echo
sudo systemctl restart ff-schedule.timer
echo
sudo systemctl restart ff-reverb.service
echo
