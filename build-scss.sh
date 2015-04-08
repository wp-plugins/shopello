#!/usr/bin/env bash

DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)

SCSS_PATH=$DIR/src/scss/
CSS_PATH=$DIR/assets/css/

for file in $(/usr/bin/find $SCSS_PATH -type f ! -name '_*'); do
    scss=$(/bin/echo $file | sed "s:"$SCSS_PATH"::") # Remove search path from filename
    css=$(/bin/echo $scss  | sed 's:scss$:css:')     # Change file-extension from scss to css

    echo "Compiling $SCSS_PATH$scss to $CSS_PATH$css"
    $DIR/vendor/bin/pscss -i=$SCSS_PATH -f=compressed < $SCSS_PATH$scss > $CSS_PATH$css
done
