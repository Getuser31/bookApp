@extends('index')

@section('content')
    <div class="container">
        <h2>List Of Collections</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
            @foreach($collections as $collection)
                <tr>
                    <th>{{$collection->name}}</th>
                    <th><a href="{{route("admin.collection.edit", $collection->id)}}">
                            <svg class="h-8 w-8 text-green-500" width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                 stroke="currentColor" fill="none"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z"/>
                                <rect x="4" y="4" width="16" height="16" rx="2"/>
                                <path d="M9 12l2 2l4 -4"/>
                            </svg>
                        </a></th>
                    <th>
                        <form action="{{ route('admin.collection.delete', $collection->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type='submit' class="h-8 w-8 text-red-500">
                                <svg class="h-8 w-8 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon
                                        points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
                            </button>
                        </form>
                    </th>
                </tr>
            @endforeach

        </table>

        <h2>Create New Collection</h2>

        <a href="{{route("admin.collection.create")}}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">New Collection</a>
    </div>

@endsection
