<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DomainModel;

class VlanModel extends Model
{
     use HasFactory;
     protected $table = 'tb_vlan'; // Assuming the table name is tb_domain
    protected $primaryKey = 'id';
    public $timestamps = false; // Disable timestamps if not used

    protected $fillable = [
        'domain',
        'vlanid',
        'vlan',
        'block_ip',
        'gateway',
        'isdeleted',
        'remark',
        'iby',
        'idt',
        'uby',
        'udt',
        'dby',
        'ddt',
    ];

    public function domainData() {
    return $this->belongsTo(DomainModel::class, 'domain');
}
}
