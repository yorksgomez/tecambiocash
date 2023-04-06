<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'country',
        'nit_type',
        'nit',
        'phone',
        'doc_image',
        'customer_image',
    ];

    public function user() : MorphOne {
        return $this->morphOne(User::class, 'user', 'role', 'role_id');
    }

}
