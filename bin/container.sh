#!/usr/bin/env bash

function initializeCommands {
    case "$1" in
        start)
            start
            ;;
        stop)
            stop
            ;;
        restart)
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

    cat .env-template |
    sed -e "s/{uid}/${uid}/" |
    sed -e "s/{gid}/${guid}/" > .env
}

function startContainer {
    #start container
    docker-compose -f docker-compose.yml up --build -d
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