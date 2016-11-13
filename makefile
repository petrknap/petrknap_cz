all: docker-stop permission-update temp-clear docker-build composer-install

composer-install:
	composer install

permission-update:
	CURRENT_USER=$$USER && \
	sudo find .                              -exec chown $$CURRENT_USER {} \;
	sudo find .      -not -path "*/vendor/*" -type d -exec chmod 755 {} \;
	sudo find .      -not -path "*/vendor/*" -type f -exec chmod 644 {} \;
	sudo find ./log                          -type d -exec chmod 777 {} \;
	sudo find ./log                          -type f -exec chmod 666 {} \;
	sudo find ./temp                         -type d -exec chmod 777 {} \;
	sudo find ./temp                         -type f -exec chmod 666 {} \;

temp-clear:
	sudo rm -rf ./temp
	git checkout -- ./temp
	make permission-update

tests-run:
	make permission-update
	make docker-stop
	sudo docker-compose run --rm web php ./vendor/bin/phpunit
	make docker-stop
	make permission-update

docker-build:
	make docker-stop
	sudo docker-compose build && \
	cat /etc/hosts | grep -v "# docker" > ./temp/hosts && \
	cat .docker/apache.conf | grep -i ServerName | \
	sed s/"ServerName.*"/"\\0 # docker"/i | sed s/"ServerName"/"127.0.0.1"/i >> ./temp/hosts && \
	sudo sh -c "cat ./temp/hosts > /etc/hosts"

docker-run:
	make docker-stop
	make permission-update
	sudo docker-compose up

docker-stop:
	sudo docker-compose stop
	sudo docker stop $$(sudo docker ps -a -q) || true

deployment: temp-clear
	make permission-update
	make docker-stop
	sudo docker-compose up -d
	sudo docker-compose run web bash ./deployment.php
	make docker-stop
	make permission-update
