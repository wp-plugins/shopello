#!/usr/bin/env bash

for file in $(/usr/bin/find src/scss/ -type f ! -name '_*'); do
    scss=$(/bin/echo $file | sed "s:src/scss/::") # Remove search path from filename
    css=$(/bin/echo $scss  | sed 's/scss$/css/')  # Change file-extension

    echo "Compiling $file to assets/css/$css"
    ./vendor/bin/pscss -i=src/scss/ -f=compressed < src/scss/$scss > assets/css/$css
done
