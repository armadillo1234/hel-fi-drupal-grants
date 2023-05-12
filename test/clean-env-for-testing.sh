#!/usr/bin/env bash

stringContain() { [ -z "$1" ] || { [ -z "${2##*$1*}" ] && [ -n "$2" ]; }; }

. /app/test/.test_env

echo $ATV_BASE_URL
echo $USER_IDT

#curl --location 'https://atv-api-hki-kanslia-atv-test.agw.arodevtest.hel.fi/v1/statistics/?services=AvustushakemusIntegraatio&types=KUVAPROJ' \
#  --header 'Accept-Encoding: utf8' \
#  --header 'X-Api-Key: ' |
#  jq -r '.results[] | "\(.id) \(.transaction_id) \(.business_id)"' |
#  while read -r line; do
#    transaction_id=$(echo "$line" | awk '{print $2}')
#    if [[ "$transaction_id" == *"$APP_ENV"* ]]; then
#      echo "$line"
#    fi
#  done

url='https://atv-api-hki-kanslia-atv-test.agw.arodevtest.hel.fi/v1/statistics/?services=AvustushakemusIntegraatio&types=KUVAPROJ'
all_results=()

while [ "$url" != "null" ]; do
  # Fetch the next page of results
  RESPONSE=$(curl --location "$url" \
               --header 'Accept-Encoding: utf8' \
               --header 'X-Api-Key: ')

  # Extract the results from the response and append them to the array
  new_results=$(echo "$RESPONSE" | jq -r '.results[] | "\(.id) \(.transaction_id) \(.business_id)"')
  all_results+=( $new_results )

  # Get the URL of the next page of results, or set it to "null" if there are no more pages
  url=$(echo "$RESPONSE" | jq -r '.next')
done

echo "RESULTS: $all_results"

#for result in "${results[@]}"; do
#    id=$(echo "$result" | cut -d ' ' -f 1)
#    transaction_id=$(echo "$result" | cut -d ' ' -f 2)
#    business_id=$(echo "$result" | cut -d ' ' -f 3)
#
#    echo "$id $transaction_id, $business_id"
#done

#    trimmed_transaction_id=$(echo "$transaction_id" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')
#    if [[ "$trimmed_transaction_id" == *"$APP_ENV"* ]]; then
#      echo "It's there!"
#    fi

#    trimmed_transaction_id=$(echo "$transaction_id" | tr -dc '[:print:]')
#    if [[ "$trimmed_transaction_id" == *"$APP_ENV"* ]]; then
#      echo "It's there!"
#    fi
#while read -r id transaction_id business_id; do
#    echo "Do whatever with ${id} ${transaction_id} ${business_id}"
#done< <($output | jq '.results | "\(.id) \(.transaction_id) \(.business_id)"')
#
#
#
#curl -s 'localhost:14002/api/sno' |
#jq -r '.satellites[].id' |
#while IFS= read -r id; do
#        curl -s 'localhost:14002/api/sno/satellite/'"$id"
#done |
#jq -r \
#        --argjson auditScore 1 \
#        --argjson suspensionScore 1 \
#        --argjson onlineScore 0.9 \
#        '.audits as $a | $a.satelliteName as $name |
#        reduce ($ARGS.named|keys[]) as $key (
#                [];
#                if $a[$key] < $ARGS.named[$key] then (
#                        . + ["\($key) below threshold: \($a[$key]) for \($name)"]
#                ) else . end
#        ) | .[]'
