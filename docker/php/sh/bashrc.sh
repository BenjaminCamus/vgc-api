#!/bin/bash

echo "export LS_OPTIONS='--color=auto'" > /etc/bash.bashrc
echo "alias grep='grep --color=auto'" >> /etc/bash.bashrc
echo "alias ls='ls \$LS_OPTIONS'" >> /etc/bash.bashrc
echo "alias ll='ls \$LS_OPTIONS -l'" >> /etc/bash.bashrc
echo "alias l='ls \$LS_OPTIONS -lA'" >> /etc/bash.bashrc
echo -e "\n" >> /root/.bashrc