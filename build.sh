#!/usr/bin/env bash

# https://api.ragie.ai/openapi.json
openapi-generator-cli generate -i https://api.ragie.ai/openapi.json -g php -o ./src/Ragie/Api --additional-properties=packageName=Ragie.Api
