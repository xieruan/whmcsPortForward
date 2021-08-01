for ((i=10000; i<=30000; i++))
do
  ehco -l 0.0.0.0:$i -r ws://2.2.2.2:$i -tt ws > /dev/null 2>&1&
done
