require 'autoprefixer-rails'

http_path = "..//"
css_dir = "../css"
images_dir = "../img"
sass_dir = "."
javascripts_dir = "../js"

output_style = :compact
line_comments = false

cache_path=".sass-cache"

on_stylesheet_saved do |file|
  css = File.read(file)

  File.open(file, 'w') { |io| io << AutoprefixerRails.process(css, browsers: ['> 1%', 'ios_saf > 6']) }

end
