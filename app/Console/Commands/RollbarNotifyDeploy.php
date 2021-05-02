<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class RollbarNotifyDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rollbar:notify-deploy';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        if (!\Config::get('rollbar.access_token')) {
            return true;
        }

        $branch = env('GIT_BRANCH', 'master');

        $client = new Client([
            'base_uri' => 'https://api.rollbar.com'
        ]);
        $client->post('/api/1/deploy/',
            [
                'form_params' => [
                    'access_token' => \Config::get('rollbar.access_token'),
                    'environment' => \Config::get('app.env', 'dev'),
                    'local_username' => get_current_user(),
                    'revision' => $this->get_current_git_commit($branch),
                ]
            ]
        );

        return true;
    }

    /**
     * Get the hash of the current git HEAD
     * @param string $branch The git branch to check
     * @return mixed Either the hash or a boolean false
     */
    function get_current_git_commit($branch)
    {
        $paths = [
            sprintf(base_path() . '/.git/refs/heads/%s', $branch),
            base_path() . '/last_git_commit'
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return file_get_contents($path);
            }
        }

        return false;
    }
}
