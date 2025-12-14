<?php

namespace App\Models;

class UserCompany extends AppModel
{
    protected $table = 'user_companies';

    protected $fillable = ['user_id', 'company_id', 'user_type'];

    public function getCreatedAtColumn()
    {
        return null;
    }
    public function getUpdatedAtColumn()
    {
        return null;
    }
}
