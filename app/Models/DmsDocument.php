<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DmsDocument extends Model {
    protected $guarded = [];
    public function folder() { return $this->belongsTo(DmsFolder::class, 'dms_folder_id'); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}