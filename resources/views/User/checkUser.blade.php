@extends('index')

@section('content')
    <ul>
        <li> userName: {{$user->name}}</li>
        <li> id: {{$user->id}}</li>
        <li> email: {{$user->email}}</li>
        <li> Role: {{$user->role->name}}</li>
    </ul>


@endsection
