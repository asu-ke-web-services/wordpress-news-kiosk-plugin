#!/usr/bin/env bash

if [ $# -lt 5 ]; then
  echo "usage: $0 <twitter_handle> <oauth_access_token> <oauth_access_token_secret> <consumer_key> <consumer_secret>"
  exit 1
fi

TWITTER_HANDLE=$1
OAUTH_ACCESS_TOKEN=$2
OAUTH_ACCESS_TOKEN_SECRET=$3
CONSUMER_KEY=$4
CONSUMER_SECRET=$5

# print out the commands being run 
set -ex


BOOTSTRAP_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

cd $BOOTSTRAP_DIR
cd ../..  
cp localsettings.default.php localsettings.php
sed -ie "s/YOUR_TWITTER_HANDLE/$TWITTER_HANDLE/g" localsettings.php
sed -ie "s/YOUR_OAUTH_ACCESS_TOKEN/$OAUTH_ACCESS_TOKEN/g" localsettings.php
sed -ie "s/YOUR_OAUTH_ACCESS_TOKEN_SECRET/$OAUTH_ACCESS_TOKEN_SECRET/g" localsettings.php
sed -ie "s/YOUR_CONSUMER_KEY/$CONSUMER_KEY/g" localsettings.php
sed -ie "s/YOUR_CONSUMER_SECRET/$CONSUMER_SECRET/g" localsettings.php