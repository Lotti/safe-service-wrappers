docker run --rm \
  --name valkey \
  -p 6379:6379 \
  -v ./valkey/conf:/usr/local/etc/valkey \
  valkey/valkey:8.1-alpine \
  valkey-server /usr/local/etc/valkey/valkey.conf
