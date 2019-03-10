<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\View\View;

class Header
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $user = Auth::user();

        $items = [];
        $notifications = 0;
        $company = null;

        // Get customer company
        if ($user->customer()) {
            $company = (object)[
                'company_name' => setting('general.company_name'),
                'company_email' => setting('general.company_email'),
                'company_address' => setting('general.company_address'),
                'company_logo' => setting('general.company_logo'),
            ];
        }

        $undereads = $user->unreadNotifications;

        foreach ($undereads as $underead) {
            $data = $underead->getAttribute('data');

            switch ($underead->getAttribute('type')) {
                case 'App\Notifications\Common\Item':
                    $items[$data['item_id']] = $data['name'];
                    $notifications++;
                    break;
            }
        }

        $view->with([
            'user' => $user,
            'notifications' => $notifications,
            'items' => $items,
            'company' => $company,
        ]);
    }
}
