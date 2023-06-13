<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "name1",
        "lastname1",
        "phone1",
        "email1",
        "name1",
        "relationship1",
        "name2",
        "lastname2",
        "phone2",
        "email2",
        "name2",
        "relationship2",
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
