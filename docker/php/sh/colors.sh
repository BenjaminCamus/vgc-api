#!/bin/bash

logoColor='\e[0;36m'
stepColor='\e[1;34;40m'
successColor='\e[1;32;42m'
warningColor='\e[1;37;41m'
varColor='\e[0;37;44m'
valColor='\e[3;36;40m'

noneColor='\e[0;m\x1B[K'

function echo_block() {
  nb=60
  step=$1
  color=$2
  tab=$3

  len=$(echo -n $step | wc -c)
  while [ $len -lt $nb ]; do
    step=$step" "
    let len=len+1
  done

  echo -e "\n"
  echo -e "${tab}${color}  $(printf ' %.0s' $(seq 1 $nb))  ${noneColor}"
  echo -e "${tab}${color}  $step  ${noneColor}"
  echo -e "${tab}${color}  $(printf ' %.0s' $(seq 1 $nb))  ${noneColor}"
  echo -e "\n"
}

function echo_step() {
  echo_block "$1" $stepColor
}

function echo_success() {
  echo_block "$1" "$successColor" "   "
}

function echo_warning() {
  echo_block "$1" "$warningColor" "   "
}
