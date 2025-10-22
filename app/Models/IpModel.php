<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VlanModel;
use App\Models\ServiceModel;
use App\Models\DeviceModel;
use App\Models\RackModel;


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
        'devicecs',
        'ipcs',
        'r_number',
        'b_number',
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
    public function DeviceData()
    {
        return $this->belongsTo(DeviceModel::class, 'device');
    }
    public function RackData()
    {
        return $this->belongsTo(RackModel::class, 'rack');
    }
}