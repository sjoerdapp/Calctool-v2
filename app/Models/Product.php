<?php

namespace BynqIO\Dynq\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    protected $table = 'product';
    protected $guarded = array('id');

    public $timestamps = false;

    public function group() {
        return $this->hasOne('SubGroup');
    }

    public function supplier() {
        return $this->hasOne('Supplier');
    }

    public function user() {
        return $this->belongsToMany('\BynqIO\Dynq\Models\User', 'product_favorite', 'product_id', 'user_id');
    }

}
