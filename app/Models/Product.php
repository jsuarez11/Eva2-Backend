<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        "name",
        "description",
        "price",
        "url_image"
    ];

    public function favoredBy(){
        return $this->belongsToMany(User::class,"favorites");
    }
}
