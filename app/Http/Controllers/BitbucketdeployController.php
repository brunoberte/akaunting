<?php

namespace App\Http\Controllers;

class BitbucketdeployController extends Controller
{
    /**
     * The name of the remote to pull from.
     *
     * @var string
     */
    private $_remote = 'origin';

    public function execute()
    {
        logger()->info('Attempting deployment...');
        logger()->info('POST: ' . print_r($_POST, true));

        $branch = env('GIT_BRANCH', 'master');

        try {
            $output = [];

            $directory = app_path() . '/';

            // Make sure we're in the right directory
            $change_dir = 'cd ' . $directory . ';';
            exec('cd ' . $directory, $output);
            logger()->info('Changing working directory ... ' . $directory . ' ... ' . implode(' ', $output));

            // Discard any changes to tracked files since our last deploy
//            exec($change_dir . 'git reset --hard HEAD', $output);
//            logger()->info('Reseting repository... '.implode(' ', $output));

            // Update the local repository
            $cmd = $change_dir . 'git pull ' . $this->_remote . ' ' . $branch;
            exec($cmd, $output);
            logger()->info('Pulling in changes... ' . $cmd . ' ... ' . implode(' ', $output));

            // Secure the .git directory
            exec($change_dir . 'chmod -R og-rx .git');
            logger()->info('Securing .git directory... ');

            $this->post_deploy();

            logger()->info('Deployment successful.');

        } catch (\Exception $e) {
            logger()->error($e);
        }


        return response('OK', 200);
    }

    private function post_deploy()
    {
        $change_dir = 'cd ' . base_path() . '/;';
        $commands = [
            'php7.4 /usr/local/bin/composer install --optimize-autoloader --no-dev',
            //            'php7.4 artisan config:cache',
            //            'php7.4 artisan route:cache',
            'php7.4 artisan cache:clear',
            'php7.4 artisan view:clear',
            'php7.4 artisan queue:restart',
        ];
        foreach ($commands as $command) {
            $cmd = $change_dir . $command;
            exec($cmd, $output);
            logger()->info($cmd . ' ... ' . implode(' ', $output));
        }

        \Artisan::call('rollbar:notify-deploy');
    }
}
