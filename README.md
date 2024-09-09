# <div align="center">LEMMON</div>

## Getting started

After VAGRANT setup create and open a new folder

### Clone the repository
`git clone git@gitlab.com:geopress/lemmon.git`

### Open a bash terminal and run:
`vagrant ssh`

### Go to project folder (lemmon in this case)
`cd lemmon`

### Install all the dependencies using composer
`composer install`

### Copy the example env file and make the required configuration changes in the .env file
`cp .env.example .env`

### Generate a new application key
`php artisan key:generate`

### Create a symlink between storage and public folder
`php artisan storage:link`

### Run the database migrations (Set the database connection in .env before migrating)
`php artisan migrate:fresh`

### EXIT VM, open a new bash terminal in project folder and run:
`npm install` and `npm run dev`


## <div align="center"><a href="https://redis.io/docs/getting-started/installation/install-redis-on-linux/">Install Redis</a></div>


### Make sure QUEUE_CONNECTION is set to redis in .env

In VM add the repository to the apt index, update it, and then install:

```
curl -fsSL https://packages.redis.io/gpg | sudo gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg

echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/redis.list

sudo apt-get update

sudo apt-get install redis

sudo systemctl enable redis-server
```

### Check if Redis works, run:

`redis-cli`
> $> ping
>
> output: PONG

### Install PHP redis extension
`sudo apt install php-redis`

## <div align="center"><a href="https://laravel.com/docs/9.x/queues#supervisor-configuration">Install Supervisor</a></div>

### Use the following command to install Supervisor:
`sudo apt-get install supervisor`

### Create a configuration file
`nano /etc/supervisor/conf.d/laravel-worker.conf`

### Add the following configuration (changing 'lemmon' with your custom data)
```
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/lemmon/artisan horizon
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/lemmon/storage/logs/worker.log
stopwaitsecs=3600
```

## <div align="center"><a href="https://laravel.com/docs/9.x/horizon#:~:text=Laravel%20Horizon%20provides%20a%20beautiful,%2C%20runtime%2C%20and%20job%20failures.">Laravel Horizon</a> & <a href="https://github.com/opcodesio/log-viewer">Log-Viewer</a></div>

### Start Supervisor

```
sudo supervisorctl reread

sudo supervisorctl update

sudo supervisorctl start laravel-worker:*
```

### Publish horizon
`php artisan horizon:publish`


Laravel horizon and laravel log-viewer are installed and configured to be accessed only by admin at the following:

```
https://{BASE_URL}/log-viewer

https://{BASE_URL}/horizon
```

## <div align="center"><a>Deploy</a></div>

### Get the new content from git
`git pull`

### Execute following commands
```
composer install

npm run dev

php artisan migrate
```

If necessary run migrations fresh and reseed

`php artisan migrate:fresh --seed`

### And clear application cache
```
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
```
