#!/bin/bash

# Remove networkinfo script
rm -f "${MUNKIPATH}preflight.d/networkinfo.sh"
rm -f "${MUNKIPATH}preflight.d/networkinfo.py"

# Remove networkinfo.plist
rm -f "${CACHEPATH}networkinfo.plist"
rm -f "${CACHEPATH}networkinfo.txt"

