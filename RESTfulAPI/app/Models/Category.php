<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Transformers\CategoryTransformer;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $transformer = CategoryTransformer::class;
    protected $dates = ['deleted_at'];
    protected $fillable = ['name', 'description'];
    protected $hidden = ['pivot'];


    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}

