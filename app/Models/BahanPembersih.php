<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BahanPembersih extends Model {
    protected $table = 'bahan_pembersih';
    protected $primaryKey = 'id_bahan';
    public $timestamps = false;
    protected $guarded = [];

    public function supplier() {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }
}
