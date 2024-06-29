@extends('index')
@section('content')
    <h2>@if($author) Edit @else Create @endif Author</h2>
    <form method="POST" action="@if($author) {{route('admin.author.update', $author->id)}} @else {{route('admin.author.store')}} @endif">
        @csrf
        @if($author)
            @method('PUT')
        @endif
        <input type="text" name="name" value="{{$author->name ?? ''}}" id="name">
        <button type="submit">@if($author) Update @else Create @endif</button>
    </form>
@endsection
