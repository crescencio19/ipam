<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\IpModel;

class CommandModel extends Model
{
    use HasFactory;

    protected $table = 'tb_command';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'ip',
        'service_command',
        'command',
        'description',
        'remark',
        'iby',
        'idt',
        'uby',
        'udt',
        'isdeleted',
        'dby',
        'ddt'
    ];

    // relasi ke tabel IP
    public function ipData()
    {
        return $this->belongsTo(IpModel::class, 'ip', 'id');
    }
}
