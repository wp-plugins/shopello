echo "----------------"
echo "Processing less files.."
sudo lessc shopello.less > ../shopello_all.css
echo "Less files compiles into shopello_all.css at $(date +%H:%M)"
echo "----------------"
