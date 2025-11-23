#!/bin/bash

# Simple health check script
set -e

PORT=${PORT:-80}
HEALTH_URL="http://localhost:$PORT/up"

echo "Testing health endpoint: $HEALTH_URL"

# Test with curl
if curl -f --max-time 10 "$HEALTH_URL"; then
    echo "✅ Health check passed!"
    exit 0
else
    echo "❌ Health check failed!"
    exit 1
fi 