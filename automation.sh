#!/bin/bash 

autorun() {
        /opt/php/php-5.4.13/bin/php -c ../php.ini -f automation.php
        exit $?
}

cd /home/alinebos/pesquisa/monitor

echo "=================================================="
echo "============== STARTING  AUTOMATION =============="
echo "=================================================="
echo "DATE: "`date` 
echo "=================================================="

count=1

while [[ $count -gt 0 ]]; do

	pids=""
	count=0
	
	for i in {1..5}; do
			( autorun ) &
			pids+=" $!"
			sleep 1
	done

	for p in $pids; do
			if wait $p; then
				echo "=================================================="
				echo "============== COMPLETE  AUTOMATION =============="
				echo "=================================================="
				echo "DATE: "`date` 
				echo "=================================================="
			else
				(( count += 1 ))
			fi
	done
done
