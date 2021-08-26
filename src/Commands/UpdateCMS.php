<?php

namespace BulldotsBulletpoint\BullseyeCmsUpdate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class UpdateCMS extends Command
{
    protected $counter = 0;
    protected $composerLock;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bullseye:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '🚀 Update the Bullseye CMS to the latest version.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        //Check if on locale environment.
        if(!app()->environment('locale')){

            $prod = $this->confirm("Running this on PRODUCTION SERVER?", true);

            if($prod){
                $this->line('❌ Don\'t update the CMS on the PRODUCTION server!');
                $this->line('❌ Update on your locale environment and pull on production server.');
                $this->line('❌ Then do \'composer install --no-dev\' to update the cms on the production server.');
                return 0;
            }

            $prod2 = $this->confirm("APP_ENV is NOT set to \'locale\' but set to 'production' or something else, so you are on PRODUCTION SERVER?", true);

            if($prod2){
                $this->line('❌ Don\'t update the CMS on the PRODUCTION server!');
                $this->line('❌ Update on your locale environment and pull on production server.');
                $this->line('❌ Then do \'composer install --no-dev\' to update the cms on the production server.');
                return 0;
            }
        }



        //Set the styles of the output
        $style = new OutputFormatterStyle(null, null, ['bold', 'underscore']);
        $this->output->getFormatter()->setStyle('bold', $style);

        /* SET THE WEB APPLICATION OFFLINE */
        $this->callAndInfo(
            '❗️ Setting the application in maintenance mode. ❗️',
            'down'
        );

        /* UPDATE THE REPO */
        $this->execAndLog('Get the latest updates from the GIT REPO:', 'git pull');

        /* UPDATE ALL PACKAGES (THIRD PARTY) */
        $this->execAndLog('♻️  Updating packages:', 'composer update --no-interaction --no-progress');

        /* ARTISAN COMMANDS TO UPDATE AND CLEAN UP THE WEBSITE */
        //$this->callAndLog('Updating database with newest values:', 'migrate --force');
        $this->title('♻️  Clearing the caches');
        $this->callAndInfo(null, 'cache:clear');
        $this->callAndInfo(null, 'auth:clear-resets');
        $this->callAndInfo(null, 'route:clear');
        $this->callAndInfo(null, 'config:clear');
        $this->callAndInfo(null, 'view:clear');
        $this->callAndInfo(null, 'optimize');

        $this->execAndInfoGit('Check if composer.lock if modified','git status | grep composer.lock');

        if($this->composerLock === "modified:   composer.lock"){

            $git = $this->confirm("Add composer.lock to git, commit and push it?", true);

            if($git){
                $this->execAndLog('Adding it to git, commit and pushing it. ', 'git add composer.lock && git commit -m "Updating Bullseye CMS" && git push');
            }else{
                $this->comment('❗️❗️ Don\'t forget to pull and do \'composer install --no-dev\' on Production server.');
            }
        }

        $this->callAndInfo('🚀  Website updated and back online! ', 'up');
        $this->line(" ");
        $this->execAndInfo('🐘  Laravel version:', 'php artisan --version');

        return true;
    }


    //HELPER METHODS
    protected function execAndLog($title, $cmd)
    {
        if ($title) {
            $this->title($title);
        }
        exec($cmd, $res);
        $this->logLines($res);
    }

    protected function execAndInfo($title, $cmd)
    {
        if ($title) {
            $this->title($title);
        }
        exec($cmd, $res);
        $this->infoLines($res);
    }

    protected function execAndInfoGit($title, $cmd)
    {
        if ($title) {
            $this->title($title);
        }
        exec($cmd, $res);

        if(empty($res)){
            $this->comment('composer.lock NOT modified.');
        }
        foreach ($res as $r) {


            $this->composerLock = trim($r);
            $this->info(trim($r));
        }
    }


    protected function callAndInfo($title, $cmd)
    {
        if ($title) {
            $this->title($title);
        }

        try {
            Artisan::call($cmd);
        } catch (\Exception $e) {
            $this->error('❌ Failed to execute command.');
            return;
        }

        $output = trim(Artisan::output());
        if ($output && $output !== "") {
            $this->info($output);
        }
    }

    protected function title($val)
    {
        $this->counter++;
        $this->line(" ");
        $this->line("<bold>{$this->counter}. $val</bold>");
    }

    protected function logLines($arr)
    {
        foreach ($arr as $r) {
            $this->line($r);
        }
    }

    protected function infoLines($arr)
    {
        foreach ($arr as $r) {
            $this->info($r);
        }
    }

}
