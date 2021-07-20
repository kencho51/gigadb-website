#!/usr/bin/env bash

# ls remote files/dir
#rclone --config=scripts/.rclone.conf ls test-cos-mac:

# memory test remote
#rclone --config=scripts/.rclone.conf test memory test-cos-mac:

# Checks the files in the source test/_data dir and remote
rclone --config=scripts/.rclone.conf check tests/_data test-cos-mac:

# Test sync dry run
#rclone --config=scripts/.rclone.conf sync --progress --dry-run tests/_data test-cos-mac:
