#!/usr/bin/env bash
# this_file: build_generatapi.sh

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SPEC_FILE="${ROOT_DIR}/openapi.json"
CHECKSUM_FILE="${ROOT_DIR}/openapi.sha256"
CONFIG_FILE="${ROOT_DIR}/openapitools.json"
OUTPUT_DIR="${ROOT_DIR}/src/Ragie/Api"
STAMP_FILE="${OUTPUT_DIR}/.regen-stamp"

if [ ! -f "${SPEC_FILE}" ]; then
  echo "Missing local OpenAPI spec at ${SPEC_FILE}." >&2
  exit 1
fi

if command -v shasum >/dev/null 2>&1; then
  CHECKSUM="$(shasum -a 256 "${SPEC_FILE}" | awk '{print $1}')"
elif command -v sha256sum >/dev/null 2>&1; then
  CHECKSUM="$(sha256sum "${SPEC_FILE}" | awk '{print $1}')"
else
  echo "Neither shasum nor sha256sum is available to compute the spec checksum." >&2
  exit 1
fi

printf '%s\n' "${CHECKSUM}" > "${CHECKSUM_FILE}"

echo "Generating Ragie PHP client from local spec..."
openapi-generator-cli generate --config "${CONFIG_FILE}" --output "${OUTPUT_DIR}"

mkdir -p "$(dirname "${STAMP_FILE}")"
printf 'spec_sha=%s\n' "${CHECKSUM}" > "${STAMP_FILE}"
printf 'generated_at=%s\n' "$(date -Iseconds)" >> "${STAMP_FILE}"

echo "Generation completed. Spec checksum recorded in ${CHECKSUM_FILE}."
