<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentMedia;
use App\Models\CommentLike;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class CommentController extends Controller
{
    
    public function userCreateComment(Request $request, $slug)
    {
        $request->validate([
            'content' => 'required|string',
            'rating' => 'required|integer|between:1,5',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,webp|max:2048',
        ]);
        $productId = Product::where('slug', $slug)->first()->id;
        // Check if user can comment
        $canComment = Order::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->whereHas('orderItems', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists();

        if (!$canComment) {
            return back()->withErrors(['message' => 'Bạn chỉ có thể bình luận khi đã mua sản phẩm này và đơn hàng đã hoàn thành.']);
        }

        $alreadyCommented = Comment::where('user_id', Auth::id())
        ->where('product_id', $productId)
        ->exists();

    if ($alreadyCommented) {
        return back()->withErrors(['message' => 'Bạn đã bình luận cho sản phẩm này rồi.']);
    }

        // Create comment
        $comment = Comment::create([
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

        // Handle media
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $mediaUrl = $file->store('media', 'public');
                CommentMedia::create([
                    'comment_id' => $comment->id,
                    'media_url' => $mediaUrl,
                    'media_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return back()->with('success', 'Bình luận đã được gửi!');
    }
    
    public function userUpdateComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
            'rating' => 'required|integer|between:1,5',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,webp|max:2048',
        ]);

        $comment = Comment::findOrFail($id);

        // Check if user owns the comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Update comment
        $comment->update([
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

        // Handle media
        if ($request->hasFile('images')) {
            // Delete old media
            $existingMedia = $comment->media;
            foreach ($existingMedia as $media) {
                Storage::disk('public')->delete($media->media_url);
                $media->delete();
            }

            // Add new media
            foreach ($request->file('images') as $file) {
                $mediaUrl = $file->store('media', 'public');
                CommentMedia::create([
                    'comment_id' => $comment->id,
                    'media_url' => $mediaUrl,
                    'media_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return back()->with('success', 'Bình luận đã được cập nhật!');
    }

    public function toggleLike(Request $request, $commentId)
    {
        $userId = Auth::id();
        $comment = Comment::findOrFail($commentId);

        $like = CommentLike::where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            CommentLike::create([
                'comment_id' => $commentId,
                'user_id' => $userId,
            ]);
            $liked = true;
        }

        $totalLikes = $comment->like->count();

        return response()->json([
            'message' => $liked ? 'Liked successfully' : 'Unliked successfully',
            'liked' => $liked,
            'totalLikes' => $totalLikes,
        ]);
    }
    public function updateStatus($id, Request $request)
    {
        $comment = Comment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:0,1,2,3,4',
        ]);

        $comment->status = $validated['status'];
        $comment->save();

        return response()->json([
            'message' => 'Comment status updated successfully!',
        ]);
    }

    /**
     * Xóa bình luận
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        $media = CommentMedia::where('comment_id', $id)->get();
        foreach ($media as $mediaItem) {
            Storage::disk('public')->delete($mediaItem->media_url);
            $mediaItem->delete();
        }

        return response()->json([
            'message' => 'Comment deleted successfully!',
        ]);
    }
    public function remove($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        $media = CommentMedia::where('comment_id', $id)->get();
        foreach ($media as $mediaItem) {
            Storage::disk('public')->delete($mediaItem->media_url);
            $mediaItem->delete();
        }

        return redirect()->route('comment.index')->with('success', 'Comment deleted successfully!');
    }


    public function index(Request $request)
    {
        $comments = Comment::with(['product', 'user', 'media', 'like'])
            ->orderBy('created_at', 'desc')
            ->get();
    
        return view('admin.comments.comment_list', compact('comments'));
    }
    
    public function store()
    {
        $users = User::all();
        $products = Product::all();
        return view('admin.comments.comment_create', compact('users', 'products'));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'content' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'status' => 'required|in:visible,hidden,reported',
            'image.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mpeg,mp4,webp|max:2048', // Validate từng file
        ]);
    
        // Tạo bình luận
        $comment = Comment::create([
            'user_id' => $request->input('user_id'),
            'product_id' => $request->input('product_id'),
            'content' => $request->input('content'),
            'rating' => $request->input('rating'),
            'status' => $request->input('status'),
        ]);
    
        // Lưu file media (nếu có)
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $mediaUrl = $file->store('media', 'public'); // Lưu file vào storage
                $mediaType = $file->getClientMimeType(); // Loại file
    
                // Lưu thông tin file vào bảng CommentMedia
                CommentMedia::create([
                    'comment_id' => $comment->id,
                    'media_url' => $mediaUrl,
                    'media_type' => $mediaType,
                ]);
            }
        }
    
        return redirect()->route('comment.index')->with('success', 'Comment created successfully!');
    }
    
    public function edit($id){
        $comment = Comment::findOrFail($id);
        $products = Product::all();
        $users = User::all();
        return view('admin.comments.comment_edit', compact('comment', 'products', 'users'));
    }

    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'product_id' => 'required|exists:products,id',
        'content' => 'required',
        'rating' => 'required|integer|min:1|max:5',
        'status' => 'required|in:visible,hidden,reported',
        'image.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mpeg,mp4,webp|max:2048',
    ]);

    $comment = Comment::findOrFail($id);

    // Cập nhật thông tin bình luận
    $comment->update([
        'user_id' => $request->input('user_id'),
        'product_id' => $request->input('product_id'),
        'content' => $request->input('content'),
        'rating' => $request->input('rating'),
        'status' => $request->input('status'),
    ]);

    // Xóa media cũ nếu có
    if ($request->filled('deleted_media')) {
        $deletedMediaIds = explode(',', $request->input('deleted_media'));
        foreach ($deletedMediaIds as $mediaId) {
            $media = CommentMedia::find($mediaId);
            if ($media) {
                // Xóa file khỏi storage
                Storage::disk('public')->delete($media->media_url);
                $media->delete();
            }
        }
    }

    // Thêm media mới nếu có
    if ($request->hasFile('image')) {
        foreach ($request->file('image') as $file) {
            $mediaUrl = $file->store('media', 'public');
            $mediaType = $file->getClientMimeType();

            CommentMedia::create([
                'comment_id' => $comment->id,
                'media_url' => $mediaUrl,
                'media_type' => $mediaType,
            ]);
        }
    }

    return redirect()->route('comment.index')->with('success', 'Comment updated successfully!');
}
    public function getCommentById($id, Request $request)
    {
        $userId = $request->user() ? $request->user()->id : null;
    
        $comment = Comment::with(['product', 'user', 'media', 'like'])
            ->findOrFail($id);
    
        // Tính tổng số lượt thích
        $totalLikes = $comment->like->count();
    
        // Kiểm tra xem người dùng hiện tại đã like bình luận này chưa
        $likedByUser = $userId ? $comment->like->where('user_id', $userId)->isNotEmpty() : false;
    
        // Gắn các giá trị mới vào đối tượng bình luận
        $comment->totalLikes = $totalLikes;
        $comment->likedByUser = $likedByUser;
    
        return response()->json($comment);
    }
    

    public function getCommentsByUser($userId)
    {
        $comments = Comment::where('idUser', $userId)
            ->with('product')

            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    public function getMedia($url) {
        if (Storage::disk('public')->exists($url)) {
            return Storage::disk('public')->url($url);
        }
        return null;
    }
   

    public function getLikes($commentId)
{
    // Kiểm tra xem bình luận có tồn tại không
    $comment = Comment::find($commentId);
    if (!$comment) {
        return response()->json(['message' => 'Comment not found'], 404);
    }

    // Lấy tổng số lượt like
    $totalLikes = CommentLike::where('comment_id', $commentId)->count();

    return response()->json([
        'comment_id' => $commentId,
        'totalLikes' => $totalLikes
    ]);
}
}