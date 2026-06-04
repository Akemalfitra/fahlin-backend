<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'message', 
        'sender', 
        'product_id'
    ];

    /**
     * Casting database types.
     * Memastikan created_at selalu terbaca sebagai datetime agar tidak error saat diformat.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke model Product.
     * Digunakan untuk menampilkan info produk di dalam chat.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Relasi ke model User.
     * Digunakan agar Admin bisa melihat Nama User (bukan cuma ID) di dashboard.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper: Menentukan apakah pengirimnya adalah Admin.
     */
    public function isAdmin(): bool
    {
        return $this->sender === 'admin';
    }
}