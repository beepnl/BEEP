#!/bin/sh 
branch=${1:-master}  

# git
git checkout $branch
git fetch --all
git reset --hard origin/$branch

./run_actions.sh $branch