@extends('index')
@section('content')
    <h1>{{ $title }}</h1>
    Author: {{ $author }} <br/>
    Date Of Publication: {{ $dateOfPublication }} <br/>
    Genre: {{ $genre }} <br/>
    Description: <p>{{ $description }}</p>
    <img src="{{ $thumbnail }}" alt="picture">
    <button id="addBook" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Book</button>

    <script>
        function sendBook() {
            // Create a form dynamically
            event.preventDefault();
            const formData = new FormData();

            // Add CSRF token for Laravel
            const csrfToken = '{{ csrf_token() }}';
            const inputs = {
                _token: csrfToken,
                title: '{{ $title }}',
                author: '{{ $author }}',
                dateOfPublication: '{{ $dateOfPublication }}',
                genre: '{{ $genre }}',
                description: '{{ $description }}',
                thumbnail: '{!! $thumbnail !!}',
                id: '{{ $id }}'
            };

            // Create and append form inputs
            for (const name in inputs) {
                formData.append(name,inputs[name].toString())
            }

            fetch("{{ route('book.googleBookStore') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            }) .then(response => response.json())
                .then(data => {
                    console.log('Success:', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('addBook').addEventListener('click', sendBook);
        });
    </script>
@endsection
