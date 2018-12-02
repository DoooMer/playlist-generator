ENVDIR=./env

all: run

run:
	cd $(ENVDIR) && docker-compose up -d php-fpm workspace && docker-compose up -d nginx
    
stop:
	cd $(ENVDIR) && docker-compose stop
	
bash:
	cd $(ENVDIR) && docker-compose exec workspace bash
