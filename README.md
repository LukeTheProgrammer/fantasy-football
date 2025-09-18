# Fantasy Football League Assistant

## Infrastructure Setup 

### Setup environment

```bash
cp .env.example .env
```

Update .env file with your database credentials and any other environment variables.

### Build docker containers

Use docker to build the containers and install dependencies so you don't have to worry about your local versions of php, node, etc.

```bash
docker compose up -d
```

### Install dependencies

```bash
docker compose exec laravel.test composer install
docker compose exec laravel.test npm install
```

### Laravel Sail

Optionally, but recommended, you can begin using [Laravel Sail](https://laravel.com/docs/12.x/sail) from this point. To do so, you will need to spin down the containers and use sail instead of docker compose.

```bash
docker compose down --rmi=all -v
sail up -d
```

As of now, there is a bug with either docker or sail that causes mysql containers to not start. To resolve this, go to the "Volumes" tab in your Docker Desktop app. Find the file `mysql.sock.lock` and delete it. Then restart the containers.

### Create database

```bash
sail artisan key:generate
sail artisan migrate --seed
```

## Data Setup

The database seeder will create a default User, load player positions, NFL teams, NFL rosters, and NFL schedules. 

Your app is now ready to import it's first league!

```bash
sail artisan league:import
```

For ESPN leagues, you will need three items: 

1. League ID - You can get this from the URL of your league. 
    - `https://fantasy.espn.com/football/league?leagueId={LEAGUE_ID}`

2. ESPN_S2 - This is a cookie and will need to be extracted from your browser's console. 

3. SWID - This is a cookie and will need to be extracted from your browser's console. 

