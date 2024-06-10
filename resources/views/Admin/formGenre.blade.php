@extends('index')
@section('content')
    <h2>@if($genre) Edit @else Create @endif Genre</h2>
    <form method="POST" action="@if($genre) {{route('admin.genre.update', $genre->id)}} @else {{route('admin.genre.store')}} @endif">
        @csrf
        @if($genre)
           @method('PUT')
        @endif
        <input type="text" name="name" value="{{$genre->name ?? ''}}" id="name">
        <button type="submit">@if($genre) Update @else Create @endif</button>
    </form>
@endsection
