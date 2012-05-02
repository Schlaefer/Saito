# Require any additional compass plugins here.
require 'bootstrap-sass'

# Set this to the root of your project when deployed:
http_path = "/"
css_dir = "stylesheets"
sass_dir = "src"
images_dir = "../img"
javascripts_dir = "javascript"
# To enable relative paths to assets via compass helper functions. Uncomment:
relative_assets = true

debug_info = true
line_numbers = true
# output_style = :compressed

# Eliminate query string on the end of image-url
asset_cache_buster do |path, file|
    
end
