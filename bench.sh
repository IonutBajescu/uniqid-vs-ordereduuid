#!/usr/bin/env bash

docker-compose up -d --force-recreate mysql
docker-compose run --rm php
