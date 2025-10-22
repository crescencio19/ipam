<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\DomainModel;

class VlansSerModel extends Model
{
    use HasFactory;

    protected $table = 'vlans_ser';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'domain',
        'vlanid',
        'vlan',
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
        return $this->belongsTo(DomainModel::class, 'domain');
    }
}
