<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\DomainModel;


class ServicesModel extends Model
{
    //
    use HasFactory;
    protected $table = 'service'; // Assuming the table name is tb_services
    protected $primaryKey = 'id';
    public $timestamps = false; // Disable timestamps if not used

    protected $fillable = [
        'domain',
        'service',
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

    public function domainData()
    {
        return $this->belongsTo(\App\Models\DomainModel::class, 'domain', 'id');
    }

}
