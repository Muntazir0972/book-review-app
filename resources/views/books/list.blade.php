@extends('layouts.app')

@section('main')

<div class="container">
    <div class="row my-5">
        <div class="col-md-3">
            @include('layouts.sidebar')
        </div>
        <div class="col-md-9">

            @include('layouts.message')

            <div class="card border-0 shadow">
                <div class="card-header  text-white">
                    Books
                </div>
                <div class="card-body pb-0">

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('books.create') }}" class="btn btn-primary">Add Book</a>
                        <form action="">
                            <div class="d-flex">
                                <input type="text" value="{{ Request::get('keyword') }}" name="keyword" class="form-control" placeholder="Keyword">
                                <button type="submit" class="btn btn-primary ms-2">Search</button>
                                <a href="{{ route('books.index') }}" class="btn btn-secondary ms-2">Clear</a>
                            </div>
                        </form>
                    </div>
                    <table class="table  table-striped mt-3">
                        <thead class="table-dark">
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th width="150">Action</th>
                            </tr>
                        <tbody>
                            @if ($books->isNotEmpty())
                            @foreach ($books as $book)

                            @php
                            if ($book->reviews_count > 0) {
                                $avgRating = $book->reviews_sum_rating/$book->reviews_count; 
                            } else {
                                $avgRating = 0;
                            }
            
                            $avgRatingPer = ($avgRating*100)/ 5;
                            @endphp 

                            <tr>
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->author }}</td>
                                <td>{{ number_format($avgRating,1) }} ({{ ($book->reviews_count) > 1 ? $book->reviews_count.' Reviews' : $book->reviews_count.' Review'  }})</td>
                                <td>
                                    @if ($book->status == 1)
                                    <span class="text-success">Active</span>
                                    @else
                                    <span class="text-danger">Block</span>

                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="btn btn-success btn-sm"><i class="fa-regular fa-star"></i></a>
                                    <a href="{{ route('books.edit',$book->id) }}" class="btn btn-primary btn-sm"><i
                                            class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <a href="#" onclick="deleteBook({{ $book->id }})" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>

                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">Books Not Found</td>
                            </tr>

                            @endif
                        </tbody>
                        </thead>
                    </table>

                    @if ($books->isNotEmpty())
                    {{ $books->links() }}
                    @endif
                    {{-- <nav aria-label="Page navigation ">
                        <ul class="pagination">
                            <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav> --}}
                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@section('script')
    <script>
        function deleteBook(id) {
           if(confirm("Are you sure you want to delete?")){
            $.ajax({
                url:'{{ route("books.destroy") }}',
                type:'delete',
                data:{id:id},
                headers:{
                    'X-CSRF-TOKEN' : '{{ csrf_token() }}'
                },
                success: function(response){
                    window.location.href = '{{ route('books.index') }}';
                }

            });
           } 
        }
    </script>
@endsection