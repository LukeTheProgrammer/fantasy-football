# Fantasy Football League Assistant

## Infrastructure Setup 

First and foremmost, you will need to install PHP and composer on the computer you plan to use to run this application. This project requires PHP 8.3 or higher, and PHP 8.4 is recommended. 

[Composer](https://getcomposer.org/)

### Setup environment

```bash
cp .env.example .env
```

Update .env file with your database credentials and any other environment variables.

### Install composer packages

```bash
composer install
```

### Build docker containers

[Laravel Sail](https://laravel.com/docs/12.x/sail)

```bash
sail up -d
```

### Generate application key

```bash
sail artisan key:generate
```

### Install npm packages

```bash
sail npm install
```

### MySQL

As of now, there is a bug with either docker or sail that causes mysql containers to not start. To resolve this, go to the "Volumes" tab in your Docker Desktop app. Find the file `mysql.sock.lock` and delete it. Then restart the containers.

You may also need to edit the `.env` to update the `DB_HOST` value to be the name of the mysql container. This is usually `fantasy-football_sail-mysql`, but you can check by using `sail ps`. 


### Create database

```bash
sail artisan migrate --seed
```

## Data Setup

The database seeder will create a default User, load player positions, NFL teams, NFL rosters, and NFL schedules. 

Your app is now ready to import it's first league!

```bash
sail artisan import:fantasy-nfl:league
```

For ESPN leagues, you will need three items: 

1. League ID - You can get this from the URL of your league. 
    - `https://fantasy.espn.com/football/league?leagueId={LEAGUE_ID}`

2. ESPN_S2 - This is a cookie and will need to be extracted from your browser's console. 

3. SWID - This is a cookie and will need to be extracted from your browser's console. 

## Final step

```bash
sail npm run dev
```

Your app is now ready to use!
