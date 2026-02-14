#!/bin/bash
# Hilal WordPress Local Setup Script
# This script waits for the Local site to be created and sets up the plugin/theme

SITE_NAME="hilal"
LOCAL_SITES_DIR="$HOME/Local Sites"
PLUGIN_SOURCE="/Users/mohamedhamdi/Work/Hilal Sighting Apps/hilal-plugin"
THEME_SOURCE="/Users/mohamedhamdi/Work/Hilal Sighting Apps/hilal-theme"

echo "=========================================="
echo "   Hilal WordPress Setup Script"
echo "=========================================="
echo ""
echo "Waiting for '$SITE_NAME' site to be created in Local..."
echo "Please create a new site named '$SITE_NAME' in the Local app."
echo ""

# Wait for the site directory to exist
while [ ! -d "$LOCAL_SITES_DIR/$SITE_NAME/app/public/wp-content" ]; do
    printf "."
    sleep 2
done

echo ""
echo "Site detected! Setting up symlinks..."

SITE_PATH="$LOCAL_SITES_DIR/$SITE_NAME/app/public"

# Create symlinks
ln -sf "$PLUGIN_SOURCE" "$SITE_PATH/wp-content/plugins/hilal-plugin"
ln -sf "$THEME_SOURCE" "$SITE_PATH/wp-content/themes/hilal-theme"

echo ""
echo "Symlinks created successfully!"
echo ""
echo "  Plugin: $SITE_PATH/wp-content/plugins/hilal-plugin"
echo "  Theme:  $SITE_PATH/wp-content/themes/hilal-theme"
echo ""
echo "=========================================="
echo "   Next Steps:"
echo "=========================================="
echo "1. Open Local app and start the '$SITE_NAME' site"
echo "2. Click 'Open Site' or visit the local URL"
echo "3. Go to WordPress Admin: /wp-admin"
echo "4. Go to Plugins > Activate 'Hilal - Islamic Moon Sighting Platform'"
echo "5. Go to Appearance > Themes > Activate 'Hilal Theme'"
echo "6. Install ACF plugin: Plugins > Add New > Search 'Advanced Custom Fields'"
echo ""
echo "Your local WordPress site should now be ready!"
echo "=========================================="
