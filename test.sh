#!/usr/bin/env bash
# this_file: test.sh

set -euo pipefail

composer install --prefer-dist --no-progress
composer lint
composer stan
composer psalm
composer test
