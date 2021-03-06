<?php

namespace App\Http\Controllers\Wizard;

use Illuminate\Routing\Controller;

class Finish extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function index()
    {
        if (setting(setting('general.wizard', false))) {
            return redirect('/');
        }

        setting()->set('general.wizard', true);

        // Save all settings
        setting()->save();

        $modules = [];

        return view('wizard.finish.index', compact('modules'));
    }
}
