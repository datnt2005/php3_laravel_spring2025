@extends('layouts.appAdmin')
@section('content')
<div class="container-fluid">
    <!-- Place your content here -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">comments</h3>
                </div>
                <a href="/admin/comments/create" class="btn btn-primary mb-3 w-25 mt-3 ms-3">Create comment</a>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Content</th>
                            <th>Media</th>
                            <th>Rating</th>
                            <th>Like</th>
                            <th>Product</th>
                            <th>Create at</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($comments as $comment)
                        <tr>
                            <td>{{ $comment->id }}</td>
                            <td>{{ $comment->user->name }}</td>
                            <td>{{ $comment->content }}</td>
                            <td>
                                @foreach($comment->media as $media)
                                    @if($media->media_type == 'image/jpeg' || $media->media_type == 'image/png' || $media->media_type == 'image/gif')
                                    <img src="{{ asset('storage/'.$media->media_url) }}" alt="Media" width="50" height="50">
                                    @else
                                    <video src="{{ asset('storage/'.$media->media_url) }}" controls width="50" height="50"></video>
                                    @endif
                                @endforeach
                            </td>
                            <td>{{ $comment->rating }} <i class="fa-solid fa-star text-warning"></i></td>
                            <td>{{ $comment->like->count() }}</td>
                            <td>{{ $comment->product->name }}</td>
                            <td>{{ $comment->created_at }}</td>
                            <td class="d-flex me-2">
                                <a href="{{ route('product.show',$comment->product->slug) }}" class="btn btn-success btn-sm me-2">View</a>
                                <form action="{{ route('comment.delete',$comment->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                                <td colspan="7" class="text-center">No comments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection