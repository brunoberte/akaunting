<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;

class BitbucketdeployController extends Controller
{

    /**
     * The name of the branch to pull from.
     *
     * @var string
     */
    private $_branch = '1.3-dev';

    /**
     * The name of the remote to pull from.
     *
     * @var string
     */
    private $_remote = 'origin';
    private $root_dir;

    public function execute()
    {
        $this->root_dir = base_path() . '/';

        \Log::info('Attempting deployment...');
        \Log::info('POST: ' . print_r($_POST, true));

        try {
            $output = [];

            $directory = base_path() . '/';

            // Make sure we're in the right directory
            $change_dir = 'cd ' . $directory . ';';
            exec('cd ' . $directory, $output);
            \Log::info('Changing working directory ... ' . $directory . ' ... ' . implode(' ', $output));

            // Discard any changes to tracked files since our last deploy
//            exec($change_dir . 'git reset --hard HEAD', $output);
//            \Log::info('Reseting repository... '.implode(' ', $output));

            // Update the local repository
            $cmd = $change_dir . 'git pull ' . $this->_remote . ' ' . $this->_branch;
            exec($cmd, $output);
            \Log::info('Pulling in changes... ' . $cmd . ' ... ' . implode(' ', $output));

            // Secure the .git directory
            exec($change_dir . 'chmod -R og-rx .git');
            \Log::info('Securing .git directory... ');

            $this->post_deploy();

            \Log::info('Deployment successful.');

        } catch (\Exception $e) {
            \Log::error($e);
        }


        return response('OK', 200);
    }

    private function post_deploy()
    {

        \Log::info('Limpando cache... ');

        //TODO: limpar cache
//      Cache::clear(false, 'acl');

        $change_dir = 'cd ' . base_path() . '/;';
        $commands = [
            '~/composer.phar install --optimize-autoloader --no-dev',
            'php artisan config:cache',
            'php artisan route:cache',
        ];
        foreach ($commands as $command) {
            $cmd = $change_dir . $command;
            exec($cmd, $output);
            \Log::info($cmd . ' ... ' . implode(' ', $output));
        }

//        $client = new Client([
//            'base_uri' => 'https://api.rollbar.com'
//        ]);
//        $client->post('/api/1/deploy/',
//            [
//                'form_params' => [
//                    'access_token' => env('ROLLBAR_TOKEN'),
//                    'environment' => env('ROLLBAR_ENV', 'dev'),
//                    'local_username' => get_current_user(),
//                    'revision' => $this->get_current_git_commit(),
//                ]
//            ]
//        );

    }

    /**
     * Get the hash of the current git HEAD
     * @param string $branch The git branch to check
     * @return mixed Either the hash or a boolean false
     */
    function get_current_git_commit($branch = 'master')
    {
        if ($hash = file_get_contents(sprintf($this->root_dir . '.git/refs/heads/%s', $branch))) {
            return $hash;
        } else {
            return false;
        }
    }
}
