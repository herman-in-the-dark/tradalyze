#!/bin/sh
# =============================================================================
# Tradalyze entrypoint — works for:
#   • direct IP access          http://192.168.1.10  (or :PORT if non-standard)
#   • FQDN access               http://tradalyze.example.com
#   • TLS reverse-proxy         https://tradalyze.example.com
#   • localhost dev             http://localhost:8080 (host maps 8080→80)
#
# Runtime env vars (set via -e or mounted .env):
#   APP_URL          Full base URL. Auto-detected from host IP when missing.
#   APP_KEY          Laravel encryption key. Auto-generated when missing.
#   TRUSTED_PROXIES  Proxy CIDRs or * for all. Defaults to *.
#   NGINX_PORT       Port nginx listens on inside the container (default 80).
# =============================================================================

ENV_FILE="/var/www/html/.env"

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------
env_get() {
    grep -m1 "^${1}=" "${ENV_FILE}" 2>/dev/null | cut -d= -f2- | sed "s/^['\"]//;s/['\"]$//"
}

env_set() {
    # Use sed+cp instead of sed -i so it works on bind-mounted files.
    # sed -i uses rename() which fails when the file is a bind mount;
    # cp writes to the existing inode, which always succeeds.
    local _tmp="/tmp/.entrypoint_env.tmp"
    if grep -q "^${1}=" "${ENV_FILE}" 2>/dev/null; then
        sed "s|^${1}=.*|${1}=${2}|" "${ENV_FILE}" > "${_tmp}" && cp "${_tmp}" "${ENV_FILE}"
    else
        printf '%s=%s\n' "${1}" "${2}" >> "${ENV_FILE}"
    fi
    rm -f "${_tmp}"
}

# ---------------------------------------------------------------------------
# 1. Ensure .env exists
# ---------------------------------------------------------------------------
if [ ! -f "${ENV_FILE}" ]; then
    [ -f "/var/www/html/.env.example" ] && cp /var/www/html/.env.example "${ENV_FILE}" || touch "${ENV_FILE}"
    echo "[entrypoint] Created .env"
fi

# ---------------------------------------------------------------------------
# 2. APP_KEY — generate if absent
# ---------------------------------------------------------------------------
CURRENT_KEY="${APP_KEY:-$(env_get APP_KEY)}"
if [ -z "$CURRENT_KEY" ]; then
    GENERATED=$(php /var/www/html/artisan key:generate --show 2>/dev/null)
    env_set APP_KEY "${GENERATED}"
    export APP_KEY="${GENERATED}"
    echo "[entrypoint] APP_KEY generated."
else
    export APP_KEY="${CURRENT_KEY}"
fi

# ---------------------------------------------------------------------------
# 3. APP_URL — auto-detect when not set to a real host
# ---------------------------------------------------------------------------
FILE_URL="$(env_get APP_URL)"
EFFECTIVE_URL="${APP_URL:-$FILE_URL}"

is_placeholder() {
    case "$1" in
        ""|"http://localhost"|"https://yourdomain.com") return 0;;
        *) return 1;;
    esac
}

if is_placeholder "$EFFECTIVE_URL"; then
    # Try multiple detection methods in order of reliability
    DETECTED_IP=""
    # Method 1: primary IP from routing table
    _r=$(ip route get 1.1.1.1 2>/dev/null)
    case "$_r" in *src*) DETECTED_IP=$(echo "$_r" | awk '/src/{for(i=1;i<=NF;i++) if($i=="src") print $(i+1)}') ;; esac
    # Reject obviously wrong values (0, 127.x.x.x)
    case "$DETECTED_IP" in ""|"0"|127.*) DETECTED_IP="" ;; esac
    # Method 2: first non-loopback global IP
    [ -z "$DETECTED_IP" ] && DETECTED_IP=$(ip -4 addr show scope global 2>/dev/null | awk '/inet/{split($2,a,"/"); print a[1]; exit}')
    # Method 3: hostname -I
    [ -z "$DETECTED_IP" ] && DETECTED_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
    [ -z "$DETECTED_IP" ] && DETECTED_IP="localhost"
    NGINX_PORT="${NGINX_PORT:-80}"
    if [ "$NGINX_PORT" = "80" ] || [ "$NGINX_PORT" = "443" ]; then
        EFFECTIVE_URL="http://${DETECTED_IP}"
    else
        EFFECTIVE_URL="http://${DETECTED_IP}:${NGINX_PORT}"
    fi
    echo "[entrypoint] APP_URL auto-detected: ${EFFECTIVE_URL}"
else
    echo "[entrypoint] APP_URL: ${EFFECTIVE_URL}"
fi

env_set APP_URL "${EFFECTIVE_URL}"
export APP_URL="${EFFECTIVE_URL}"

# When APP_URL uses https, tell Laravel to force https scheme in generated URLs
case "$APP_URL" in
    https://*) env_set FORCE_HTTPS "true"  ;;
    *)         env_set FORCE_HTTPS "false" ;;
esac

# ---------------------------------------------------------------------------
# 4. Trusted proxies
# ---------------------------------------------------------------------------
env_set TRUSTED_PROXIES "${TRUSTED_PROXIES:-*}"

# ---------------------------------------------------------------------------
# 5. SQLite setup
# ---------------------------------------------------------------------------
DB_CONN="${DB_CONNECTION:-$(env_get DB_CONNECTION)}"
DB_PATH="${DB_DATABASE:-$(env_get DB_DATABASE)}"

if [ "$DB_CONN" = "sqlite" ] && [ -n "$DB_PATH" ] && [ ! -f "$DB_PATH" ]; then
    mkdir -p "$(dirname "$DB_PATH")"
    touch "$DB_PATH"
    chmod 664 "$DB_PATH"
    echo "[entrypoint] SQLite database created."
fi

if [ "$DB_CONN" = "sqlite" ]; then
    chown -R www-data:www-data /var/www/html/database 2>/dev/null || true
    chmod -R 775 /var/www/html/database 2>/dev/null || true
fi

# ---------------------------------------------------------------------------
# 6. Permissions on host-mounted volumes
# ---------------------------------------------------------------------------
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# ---------------------------------------------------------------------------
# 7. Laravel bootstrap
# ---------------------------------------------------------------------------
php /var/www/html/artisan config:clear 2>/dev/null || true
php /var/www/html/artisan cache:clear  2>/dev/null || true
php /var/www/html/artisan storage:link --force 2>/dev/null || true
php /var/www/html/artisan migrate --force 2>/dev/null || true

echo "[entrypoint] Starting supervisord..."
exec "$@"
