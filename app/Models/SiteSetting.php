<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'whatsapp_number',
        'instagram_url',
        'app_install_url',
        'hero_media_type',
        'hero_media_path',
    ];
}
