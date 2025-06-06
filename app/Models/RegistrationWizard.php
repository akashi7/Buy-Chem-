<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class RegistrationWizard extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'unique_identifier',
        'title',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'nationality',
        'phone_number',
        'profile_picture',
        'country_of_residence',
        'city',
        'postal_code',
        'apartment_name',
        'room_number',
        'is_expatriate',
        'two_factor_verified',
        'password',
        'current_step'
    ];
}
