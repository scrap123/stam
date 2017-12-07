#rm -r -f __pycache__
#python -m pytest --capture=no
#if ["$1"]; then
#   echo "aaa"
#else
#    echo "tttyyyy"
#fi
python -m pytest --capture=no -k $1