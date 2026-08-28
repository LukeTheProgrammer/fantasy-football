# Fantasy Football League Assistant

## Infrastructure Setup 

First and foremmost, you will need to install PHP and composer on the computer you plan to use to run this application. This project requires PHP 8.3 or higher, and PHP 8.4 is recommended. 

[Composer](https://getcomposer.org/)

### Setup environment

```bash
cp .env.example .env
```

Update .env file with your database credentials and any other environment variables.

It is especially important to update your DB variables as the values are used when creating the database container. 

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

You may also need to edit the `.env` to update the `DB_HOST` value to be the name of the mysql container. This is usually `mysql`, but you can check by using `sail ps` and finding the `SERVICE` name of the mysql container. 


### Create database

```bash
sail artisan migrate --seed
```

## Data Setup

The database seeder will create a default User, load player positions, NFL teams, NFL rosters, and NFL schedules. 

Your app is now ready to import it's first league!

For ESPN leagues, you will need three items: 

1. League ID - You can get this from the URL of your league. 
    - `https://fantasy.espn.com/football/league?leagueId={LEAGUE_ID}`

2. ESPN_S2 - This is a cookie and will need to be extracted from your browser's console. 

3. SWID - This is a cookie and will need to be extracted from your browser's console. 

Once you have these values, it can be helpful to put them in your .env file. 

```bash
ESPN_DEFAULT_S2=
ESPN_DEFAULT_SWID=
ESPN_DEFAULT_LEAGUE_ID=
```

You do not have to set these values, as the creation command will prompt you for them. 

```bash
sail artisan import:fantasy-nfl:league
```

## Live draft night

The auction draft room can pull picks straight from ESPN instead of having every
sale typed in. Two extra processes have to be running for it:

```bash
sail artisan reverb:start   # websocket server the room listens on
sail artisan queue:work     # runs the sync loop
```

On the LAN dev server both run as systemd units instead — `system/reverb.service`
beside the existing `system/queue.service`, with nginx proxying `/app` and
`/apps` through to Reverb (`system/fantasy.local.conf`), so `REVERB_HOST` is
`fantasy.local` and `REVERB_PORT` is `80` there.

Then press **Sync from ESPN** in the draft room. It polls ESPN every five
seconds, writes in picks the board does not have yet, and stops on its own when
ESPN reports the draft complete. The manual sale form keeps working the whole
time — ESPN publishes a pick a beat after the room hears it, and a player who
cannot be matched is counted beside the button rather than silently dropped.

## Final step

```bash
sail npm run dev
```

Your app is now ready to use!
