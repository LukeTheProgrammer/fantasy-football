<?php

namespace App\Console\Commands;

use App\Facades\Espn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Facades\Espn\Exceptions\ESPNAccessDenied;
use App\Facades\Espn\Exceptions\ESPNInvalidLeague;
use App\Facades\Espn\Exceptions\ESPNUnknownError;
use App\Facades\Espn\Logging\FileLogger;
use App\Facades\Espn\EspnFantasyRequests;

class EspnPullLeagueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'espn:pull-league';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls a league from ESPN';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lid = 691509;
        $fl = Espn::fantasyLeague($lid);

        $x = $fl->getMatchup();
        dump(get_class($x), array_keys($x->toArray()));

        $x = $fl->getRoster();
        dump(get_class($x), array_keys($x->toArray()));

        $x = $fl->getSettings();
        dump(get_class($x), array_keys($x->toArray()));

        $x = $fl->getStandings();
        dump(get_class($x), array_keys($x->toArray()));

        $x = $fl->getTeams();
        dump(get_class($x), array_keys($x->toArray()));

        // $this->writeFile($lid, 'mMatchup', $fl->getMatchup());
        // $this->writeFile($lid, 'mRoster', $fl->getRoster());
        // $this->writeFile($lid, 'mSettings', $fl->getSettings());
        // $this->writeFile($lid, 'mStandings', $fl->getStandings());
        // $this->writeFile($lid, 'mTeam', $fl->getTeams());

        dd('done');


        $r = Http::get('https://fantasy.espn.com/apis/v3/games/ffl/seasons/2025/segments/0/leagues/691509?view=mTeam');
        dd($r->status());




        // https://fantasy.espn.com/football/team?leagueId=691509&teamId=1
        $leagueId = 691509;
        $teamId = 1;

        $url = 'https://fantasy.espn.com/football/team';

        $cookie = 'check=true; userZip=66062; country=us; hashedIp=15c9f2d589b0310325ad9a7819125c027f941e734c72f307007020748008fce1; tveProviderName=DTV; AMCVS_EE0201AC512D2BE80A490D4C%40AdobeOrg=1; s_cc=true; SWID={D5956E6B-2C41-428B-9E26-67AC379841B0}; ESPN-ONESITE.WEB-PROD-ac=XUS; espnAuth={"swid":"{D5956E6B-2C41-428B-9E26-67AC379841B0}"}; region=ccpa; _dcf=1; s_ensCDS=0; s_ensNSL=0; s_ensRegion=ccpa; usprivacy=1YNY; IR_gbd=espn.com; _cb=pf39cDE4Mkrx0sIC; _scor_uid=5aa44dc647b040c5bd37ea5f781b2265; _cc_id=ba7c22663c5fe1b902d4fff779bd7eb1; panoramaId_expiry=1757302360725; panoramaId=bc302a818d3601d65d4660db93a3185ca02c87c51eea0af3a187699ad839c5ce; panoramaIdType=panoDevice; _gcl_au=1.1.916297253.1756859964; device_c71519a7=ded7bc26-079e-43b2-9a01-f1076ab6a0a2; espn_s2=AEBcD5rYZzi36MjaNKx%2F1T1ec29kK4EMuBGqyze4pV031pBrxbV5pyIbF7Kx2I94yzUPWD5gi%2B1F%2B5c5wsJ1looEW8sMJWxk9y03pn6ZgK8r3%2FlH2Ey8XoyhSEV4sw7aMRAs46Rat0hYpEQXjUal%2BByu7L%2Bs4VKdfjJD14Dox1sFL1Z1GtoW5sgLp6LSW7nesZvJittt9742TeVuY12ySHYETP%2BicxtDKCGdvvcD%2FtPxMlTqjzsLXq39zTuis%2FAu6I6uHb0UMVxP8uLQ18vBK1s%2BgUrqrhUdqdTX6OOpgiuMjg%3D%3D; ab.storage.userId.96ad02b7-2edc-4238-8442-bc35ba85853c=g%3A%257BD5956E6B-2C41-428B-9E26-67AC379841B0%257D%7Ce%3Aundefined%7Cc%3A1756864505722%7Cl%3A1756864505723; ab.storage.deviceId.96ad02b7-2edc-4238-8442-bc35ba85853c=g%3A3ef248cc-3ffc-97e8-4d41-f69845e4d06e%7Ce%3Aundefined%7Cc%3A1756864505724%7Cl%3A1756864505724; s_sq=%5B%5BB%5D%5D; ab.storage.sessionId.96ad02b7-2edc-4238-8442-bc35ba85853c=g%3Ab1e8b7bc-445c-8b96-ad89-ceb0f5176a39%7Ce%3A1756867180270%7Cc%3A1756864505723%7Cl%3A1756865380270; dtcAuth=; kona_v3_environment_season_ffl={"leagueId":691509,"seasonId":null}; kona_v3_teamcontrol_ffl={"leagueId":691509,"seasonId":2025,"teamId":1}; __fitt-sess-device.prod=93b3bb37-7259-4a2b-bd18-e6775c41bbfd; block.check=false%7Cfalse; espn-prev-page=fantasy%3Afootball%3Aleague%3Amy%20team; _chartbeat2=.1756697559760.1757129764714.101001.D_Hy-_DgDprgCKSw-bZtx47CSApm7.1; _cb_svref=external; s_ensNR=1757129764834-Repeat; OptanonConsent=isGpcEnabled=0&datestamp=Fri+Sep+05+2025+22%3A36%3A05+GMT-0500+(Central+Daylight+Time)&version=202407.2.0&browserGpcFlag=0&isIABGlobal=false&hosts=&consentId=86ed249c-93cc-4c63-825a-1c33313aead6&interactionCount=1&isAnonUser=1&landingPath=NotLandingPage&groups=C0001%3A1%2CC0003%3A1%2CBG407%3A1%2CC0002%3A1%2CC0004%3A1%2CC0005%3A1&AwaitingReconsent=false; IR_9070=1757129765085%7C0%7C1757129765085%7C%7C; AMCV_EE0201AC512D2BE80A490D4C%40AdobeOrg=-50417514%7CMCMID%7C80850606737766636653809669732149984142%7CMCAID%7CNONE%7CMCOPTOUT-1757136965s%7CNONE%7CvVersion%7C5.5.0%7CMCAAMLH-1757734565%7C7%7CMCAAMB-1757734565%7Cj8Odv6LonN4r3an7LhD3WZrU1bUpAkFkkiY1ncBR96t2PTI; ESPN-ONESITE.WEB-PROD.token=5=eyJhY2Nlc3NfdG9rZW4iOiJleUpyYVdRaU9pSm5kV1Z6ZEdOdmJuUnliMnhzWlhJdExURTJNakF4T1RNMU5EUWlMQ0poYkdjaU9pSkZVekkxTmlKOS5leUpxZEdraU9pSk5jazU2WDNkRVdHcHpjall5Y3pSeGRXcFRjblJCSWl3aWFYTnpJam9pYUhSMGNITTZMeTloZFhSb0xuSmxaMmx6ZEdWeVpHbHpibVY1TG1kdkxtTnZiU0lzSW1GMVpDSTZJblZ5Ympwa2FYTnVaWGs2YjI1bGFXUTZjSEp2WkNJc0luTjFZaUk2SW50RU5UazFOa1UyUWkweVF6UXhMVFF5T0VJdE9VVXlOaTAyTjBGRE16YzVPRFF4UWpCOUlpd2lhV0YwSWpveE56VTNNVEk1TnpZMUxDSnVZbVlpT2pFM05UWTROakkxTnpBc0ltVjRjQ0k2TVRjMU56SXhOakUyTlN3aVkyeHBaVzUwWDJsa0lqb2lSVk5RVGkxUFRrVlRTVlJGTGxkRlFpMVFVazlFSWl3aWJHbGtJam9pTjJFd1lXSmxOekV0TVRNMU55MDBZMkZoTFdFMk1qY3RaVGszWmpSbU5EazNOV05sSWl3aVkyRjBJam9pWjNWbGMzUWlMQ0pwWkdWdWRHbDBlVjlwWkNJNklqZ3hNMlk0WXprMExUTTNPVEl0TkRWalpTMDVNR1E1TFdNMFpXWmtObUl3TlRsaU9TSjkuaUo2czF1THVvMW56ei1jVEdtQ2tYQlVNYm9fVTFfMWFVTjJTdnFYWXIxaWt4NHdQVzhDNS1rSS0wM2lsM2tIdy1wMzRIdEJBbVdOUVktQ0gtTmFOTWciLCJyZWZyZXNoX3Rva2VuIjoiZXlKcmFXUWlPaUpuZFdWemRHTnZiblJ5YjJ4c1pYSXRMVEUyTWpBeE9UTTFORFFpTENKaGJHY2lPaUpGVXpJMU5pSjkuZXlKcWRHa2lPaUpOZW5Bd1owZGlZMEl6UlRRNFVYUlZTRkIyZUdkbklpd2ljM1ZpSWpvaWUwUTFPVFUyUlRaQ0xUSkROREV0TkRJNFFpMDVSVEkyTFRZM1FVTXpOems0TkRGQ01IMGlMQ0pwYzNNaU9pSm9kSFJ3Y3pvdkwyRjFkR2d1Y21WbmFYTjBaWEprYVhOdVpYa3VaMjh1WTI5dElpd2lZWFZrSWpvaWRYSnVPbVJwYzI1bGVUcHZibVZwWkRwd2NtOWtJaXdpYVdGMElqb3hOelUzTVRJNU56WTFMQ0p1WW1ZaU9qRTNOVFk0TmpJMU56QXNJbVY0Y0NJNk1UYzNNalk0TVRjMk5Td2lZMnhwWlc1MFgybGtJam9pUlZOUVRpMVBUa1ZUU1ZSRkxsZEZRaTFRVWs5RUlpd2lZMkYwSWpvaWNtVm1jbVZ6YUNJc0lteHBaQ0k2SWpkaE1HRmlaVGN4TFRFek5UY3ROR05oWVMxaE5qSTNMV1U1TjJZMFpqUTVOelZqWlNJc0ltbGtaVzUwYVhSNVgybGtJam9pT0RFelpqaGpPVFF0TXpjNU1pMDBOV05sTFRrd1pEa3RZelJsWm1RMllqQTFPV0k1SW4wLmo2ZUhXT3p0YnE4a0E5MUk3THNZUkRuTG1TWUVNejRJZE10UE5WODVyTlRxY0g3Y0l5RVBoekV4Mk9nRHhOdVFzdVk0QnBhbk5zQ2FET2tVRE5nNkh3Iiwic3dpZCI6IntENTk1NkU2Qi0yQzQxLTQyOEItOUUyNi02N0FDMzc5ODQxQjB9IiwidHRsIjo4NjM5OSwicmVmcmVzaF90dGwiOjE1NTUxOTk5LCJoaWdoX3RydXN0X2V4cGlyZXNfaW4iOjAsImluaXRpYWxfZ3JhbnRfaW5fY2hhaW5fdGltZSI6MTc1Njg2MjU3MDAwMCwiaWF0IjoxNzU3MTI5NzY1MDAwLCJleHAiOjE3NTcyMTYxNjUwMDAsInJlZnJlc2hfZXhwIjoxNzcyNjgxNzY1MDAwLCJoaWdoX3RydXN0X2V4cCI6MTc1Njg2NDM3MDAwMCwic3NvIjpudWxsLCJhdXRoZW50aWNhdG9yIjpudWxsLCJsb2dpblZhbHVlIjpudWxsLCJjbGlja2JhY2tUeXBlIjpudWxsLCJzZXNzaW9uVHJhbnNmZXJLZXkiOiJtcHpVYm14b2pCdW85ZERvbi1femdhalM1cHEzUFNrYnJwcndVck9Sb25KUzdpMTR5eGdtVUNzcHQ5RS1kTWFOTnNlN3U5Rl9nRTBLVUVqNkNWZGNSN2dNYlNNQWNlVU5wWS1nUENTSm1fd2lfS2VoaDU4IiwiY3JlYXRlZCI6IjIwMjUtMDktMDZUMDM6MzY6MDUuNjYwWiIsImxhc3RDaGVja2VkIjoiMjAyNS0wOS0wNlQwMzozNjowNS42NjBaIiwiZXhwaXJlcyI6IjIwMjUtMDktMDdUMDM6MzY6MDUuMDAwWiIsInJlZnJlc2hfZXhwaXJlcyI6IjIwMjYtMDMtMDVUMDM6MzY6MDUuMDAwWiJ9|eyJraWQiOiJndWVzdGNvbnRyb2xsZXItLTE2MjAxOTM1NDQiLCJhbGciOiJFUzI1NiJ9.eyJqdGkiOiI5RFVMc0drOHJBOFRLLTZ6VG5NZGN3IiwiaXNzIjoiaHR0cHM6Ly9hdXRoLnJlZ2lzdGVyZGlzbmV5LmdvLmNvbSIsImF1ZCI6IkVTUE4tT05FU0lURS5XRUItUFJPRCIsInN1YiI6IntENTk1NkU2Qi0yQzQxLTQyOEItOUUyNi02N0FDMzc5ODQxQjB9IiwiaWF0IjoxNzU3MTI5NzY1LCJuYmYiOjE3NTY4NjI1NzAsImV4cCI6MTc1NzIxNjE2NSwiY2F0IjoiaWR0b2tlbiIsImVtYWlsIjoibHVrZWhlbnJ5NEBnbWFpbC5jb20iLCJpZGVudGl0eV9pZCI6IjgxM2Y4Yzk0LTM3OTItNDVjZS05MGQ5LWM0ZWZkNmIwNTliOSJ9.nTm1UkP94ShRMeK5i36AZqHm9b5FqPUObK3sIqA3___gmV-Nca3CoEBNG5AZTg24zS45aFBbiGANF0rLUe16rA; ESPN-ONESITE.WEB-PROD.idn=007e8d241e; connectId={"ttl":86400000,"lastUsed":1757129765969,"lastSynced":1757129765969}; cto_bundle=OcjL9180UHl4WFlKQVM3TnJqJTJGRUh2MmxxMEhTRHRnTGlmSExPUm9ZJTJGVXVVbm0wWXV6OGVsVndaMERYSllNZnNMRkw3OEZra3FBeTRqY29aa1VMMWpKN1ZxZk9SJTJGM1Nod2hWZng4YTc1T2VZbyUyRjVwUTQzV1RRSXZmWXJMeGQ1VjBGcEFadjluMGUlMkZSJTJGbFpQdWw0anc3MUtUM0ZkdmlzejJZSzY2ODBnNzdER1ZUWUxuNVM5alFWeTFTMkRWQjNvcSUyRnRHam1GcGVlZGpJWCUyQmxCQjlkU1FkdFE3QSUzRCUzRA; __gads=ID=e9b847b2df9b15df:T=1756859963:RT=1757129765:S=ALNI_MaTMfNXck3ccEzdT1Dh6sdbKY9d5A; __gpi=UID=00001264ab1a033d:T=1756859963:RT=1757129765:S=ALNI_MYMdOdu97gNLJ4GTaYcJfAksugLGQ; __eoi=ID=24298ba555c185b1:T=1756859963:RT=1757129765:S=AA-AfjazBwUOWP-X4oX9u5IpcsaK; espnCreative138271411242=true';

        $response = Http::withHeaders([
            'Cookie' => $cookie,
        ])->get($url, ['leagueId' => $leagueId, 'teamId' => $teamId]);

        dd($response->status());

        $file = file_put_contents(storage_path('league.txt'), $response->body());

        dd($file);
        return $file;
    }

    public function writeFile($leagueId, $type, $data)
    {
        return file_put_contents(
            storage_path('data/espn/league-' . $leagueId . '-' . $type . '.json'),
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }
}
