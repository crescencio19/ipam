<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DomainModel;
use App\Models\ServicesModel;

class ServiceModel extends Model
{
    use HasFactory;
    protected $table = 'tb_service'; // Assuming the table name is tb_domain
    protected $primaryKey = 'id';
    public $timestamps = false; // Disable timestamps if not used

    protected $fillable = [
        'domain',
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
    public function domainData()
    {
        return $this->belongsTo(DomainModel::class, 'domain');
    }
    // each tb_service row stores a service id -> belongsTo the services master table
    public function servicesData()
    {
        // kolom `service` di tb_service menyimpan id dari tabel master services
        return $this->belongsTo(\App\Models\ServicesModel::class, 'service', 'id');
    }
}
