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
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/book/store'; // Ensure this is the correct route

            // Add CSRF token for Laravel
            const csrfToken = '{{ csrf_token() }}';
            const inputs = {
                _token: csrfToken,
                title: '{{ $title }}',
                author: '{{ $author }}',
                dateOfPublication: '{{ $dateOfPublication }}',
                genre: '{{ $genre }}',
                description: '{{ $description }}',
                thumbnail: '{{ $thumbnail }}'
            };

            // Create and append form inputs
            for (const name in inputs) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = inputs[name].toString();  // Convert to string to avoid type issues
                form.appendChild(input);
            }

            // Log the form to the console for debugging
            console.log('Form ready to be submitted:', form);

            // Append the form to the body and submit it
            document.body.appendChild(form);

            console.log('Submitting the form');
            form.submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('addBook').addEventListener('click', sendBook);
        });
    </script>
@endsection
