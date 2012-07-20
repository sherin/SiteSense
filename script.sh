!/bin/bash
echo "Checking in..."
git add .
if [ -z "$1" ]
then
git commit -a -m "cleanup"
else
git commit -a -m "$1"
fi
git push
echo "Done!"
