<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'product_id',
        'user_id',
        'parent_id',
        'content',
        'rating',
        'likes',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * Quan hệ với User (người bình luận)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ với Product (sản phẩm bình luận)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Quan hệ với Comment (bình luận cha)
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Quan hệ với Comment (các bình luận con)
     */
    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
    public function media()
    {
        return $this->hasMany(CommentMedia::class, 'comment_id');
    }
    public function like()
    {
        return $this->hasMany(CommentLike::class , 'comment_id');
    }
    

}
