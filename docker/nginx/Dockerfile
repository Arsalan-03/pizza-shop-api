# =============================================================================
# Nginx image for production — config baked in, no host mounts required.
# =============================================================================
FROM nginx:1.27-alpine

# Bake the site configuration into the image.
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
