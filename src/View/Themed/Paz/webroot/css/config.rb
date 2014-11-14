# Require any additional compass plugins here.
# Set this to the root of your project when deployed:
http_path = "/"
css_dir = "stylesheets"
sass_dir = "src"
images_dir = "../img"
javascripts_dir = "javascript"
# To enable relative paths to assets via compass helper functions. Uncomment:
relative_assets = true

#debug_info = false
line_comments = false
# output_style = :compact
output_style = :compressed

# Eliminate query string on the end of image-url
asset_cache_buster do |path, file|

end

# 10.9 Mavericks compile failure
# see: http://hugo.castanho.me/code/invalid-us-ascii-character-xe2/
Encoding.default_external = "utf-8"
