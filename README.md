proektus api

docker-compose up -d 
docker-compose exec app php artisan migrate (to create tables)
docker-compose exec app php artisan storage:link to link storage to public (static res)

make sure .env is filled with passwords, ports and etc. for redis + postgres.