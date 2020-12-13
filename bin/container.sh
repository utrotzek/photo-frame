#!/usr/bin/env bash
set -e
rootPath="$( dirname $(dirname "$(readlink -f "$0")"))"

skipInstall=0

function initializeCommands {
    case "$1" in
        start)
            initStartOptions "$@"
            start
            ;;
        stop)
            stop
            ;;
        restart)
            initStartOptions "$@"
            restart
            ;;
        status)
            status
        ;;
    esac
}

function copyEnvExample {
    echo "parse .env template"

    uid=$(id -u)
    guid=$(id -g)

    if [[ ! -f ${rootPath}/.env ]]; then
        cat .env-template |
        sed -e "s/{uid}/${uid}/" |
        sed -e "s/{gid}/${guid}/" > .env
    fi
}

function startContainer {
    #start container
    docker-compose -f docker-compose.yml up --build -d
    installEnvironment
}

function installEnvironment {
    if [[ skipInstall -eq 0 ]]; then
        ${rootPath}/bin/composer.sh install
        ${rootPath}/bin/npm.sh install
        ${rootPath}/bin/npm.sh run dev
    fi
}

function initStartOptions {
    #parse options
    while [ $# -gt 0 ]; do
      case "$1" in
        --skip-install|-s)
            echo -e "\e[33mskipping install scripts (composer/node js)\e[39m"
          skipInstall=1
          ;;
        --help)
            echo "start|restart --skip-install (skips composer and npm install)"
            exit 0
          ;;
      esac
      shift
    done
}

function start {
    copyEnvExample
    startContainer
}

function stop {
    docker-compose -f docker-compose.yml stop
}

function restart {
    stop
    start
}

function status {
    docker-compose -f  docker-compose.yml ps
}

function main {
    initializeCommands $@


}

main $@