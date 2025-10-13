<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VlanModel;
use App\Models\ServiceModel;

class IpModel extends Model
{
    use HasFactory;
    protected $table = 'tb_ip'; // Assuming the table name is tb_domain
    protected $primaryKey = 'id';
    public $timestamps = false; // Disable timestamps if not used

    protected $fillable = [
        'vlanid',
        'vlan',
        'ip',
        'service',
        'rack',
        'bandwith',
        'device',
        'location',
        'isdeleted',
        'remark',
        'iby',
        'idt',
        'uby',
        'udt',
        'dby',
        'ddt',
    ];

    public function ServiceData()
    {
        return $this->belongsTo(ServiceModel::class, 'service');
    }
    public function VlanData()
    {
        return $this->belongsTo(VlanModel::class, 'vlan');
    }
}
