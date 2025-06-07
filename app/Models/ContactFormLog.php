<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactFormLog extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'email',
        'title',
        'message',
        'ip_address',
        'user_agent',
        'is_spam',
        'email_sent',
    ];
    
    protected $casts = [
        'is_spam' => 'boolean',
        'email_sent' => 'boolean',
    ];
}
