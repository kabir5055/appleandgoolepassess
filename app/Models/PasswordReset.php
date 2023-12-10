<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    public $token = 'password_reset_tokens';
    public $timestamps = false;
//    protected

    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];
}
