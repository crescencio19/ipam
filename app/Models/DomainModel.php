<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainModel extends Model
{
    use HasFactory;

    protected $table = 'tb_domain';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'domain',
        'description',
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
