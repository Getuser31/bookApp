@extends('index')
@section('content')
    <h2>@if($collection) Edit @else Create @endif Genre</h2>
    <form method="POST" action="@if($collection) {{route('admin.collection.update', $collection->id)}} @else {{route('admin.collection.store')}} @endif">
        @csrf
        @if($collection)
            @method('PUT')
        @endif
        <input type="text" name="name" value="{{$collection->name ?? ''}}" id="name">
        <button type="submit">@if($collection) Update @else Create @endif</button>
    </form>
@endsection
