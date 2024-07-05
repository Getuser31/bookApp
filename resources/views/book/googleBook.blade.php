@extends('index')

@section('content')

    <h1>{{$title}}</h1>

    Author : {{$author}} <br/>
    Date Of Publication : {{$dateOfPublication}} <br/>
    genre: {{$genre}} <br/>
    description: <p>{{$description}}</p>

    <img src="{{$thumbnail}}" alt="picture">


@endsection
