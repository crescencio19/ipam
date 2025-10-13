<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceModel extends Model
{
    use HasFactory;
    protected $table = 'tb_service'; // Assuming the table name is tb_domain
    protected $primaryKey = 'id';
    public $timestamps = false; // Disable timestamps if not used

    protected $fillable = [
        'service',
        'description',
        'customer',
        'location',
        'longlat',
        'isdeleted',
        'remark',
        'iby',
        'idt',
        'uby',
        'udt',
        'dby',
        'ddt',
    ];
}
