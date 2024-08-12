@extends('index')


@section('content')

    <h1>{!! $book->title !!}</h1>
    <div>
        @if (file_exists(public_path($book->picture)))
            <img src="{{ asset('/'.$book->picture) }}" alt="book picture"
                 style="float: left; max-width: 200px; height: 100%; object-fit: contain; margin-right: 10px;">
        @else
            <img src="{{$book->picture}}" alt="book picture"
                 style="float: left; max-width: 200px; height: 100%; object-fit: contain; margin-right: 10px;">
        @endif
        <ul>
            <li><b>Date de publication: </b>{{ $book->date_of_publication}}</li>
            <li><b>Auteur: </b>{{ $book->author->name }}</li>
            <li><b>Synopsis: </b>
                <p>{!! $book->description !!}</p></li>
            <li><b>Genre: </b>{{collect($book->genres)->pluck('name')->implode(' / ')}}</li>
            @if($book->collection)
                <li><b>Collection</b>{{ $book->collection->name }}</li>
            @endif

            @if($belongToUser)
                <li><b>Progression: </b>
                    <span id="progressionDisplay">{{$progression}}</span>
                    <form id="progressionForm">
                        <meta name="csrf-token" content="{{ csrf_token() }}">
                        <label for="progressionField">Set Progression:</label>
                        <input type="number" id="progressionField" name="progressionField" min="0" max="100">
                        <input type="hidden" id="bookId" value="{{$book->id}}">
                        <input type="submit" value="Update"
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    </form>
                </li>
                <li><b>Rating</b>
                    <span id="ratingDisplay">{{ $rating ?? '' }}</span>
                    <form id="ratingForm">
                        <meta name="csrf-token-rating" id='csrf-token-rating' content="{{csrf_token()}}">
                        <label for="rating">Give a rate</label>
                        <input type="number" id="ratingField" name="ratingField" min="0" max="10">
                        <input type="hidden" id="bookId" value="{{$book->id}}">
                        <input type="submit" value="Update"
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    </form>
                </li>
            @else
                <a href="{{route('book.addBookPost', ['id' => $book->id])}}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">add book</a>
            @endif
        </ul>
        <br>
    </div>

    <script>
        document.getElementById('progressionForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const progressionValue = document.getElementById('progressionField').value;
            // Retrieve the CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const bookId = document.getElementById('bookId').value;

            //AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST',
                '/updateProgression', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('progressionDisplay').innerHTML = progressionValue;
                }
            }
            xhr.send('progression=' + progressionValue + '&bookId=' + bookId);
        });
    </script>

    <script>
        document.getElementById('ratingForm').addEventListener('submit', async function(e) {
            e.preventDefault(); // To prevent the default form submission

            async function fetchCSRFToken() {
                try {
                    const response = await fetch('http://localhost:8000/sanctum/csrf-cookie', {
                        credentials: 'include', // Ensure cookies are sent and received
                    });
                    if (!response.ok) {
                        throw new Error(`Network response was not ok (${response.status})`);
                    }
                    // Store the CSRF token from the response headers
                    return response.headers.get('X-XSRF-TOKEN');
                } catch (error) {
                    console.error('Fetch CSRF Token Error:', error);
                    throw error;  // Let the error propagate
                }
            }

            function getCookie(name) {
                const matches = document.cookie.match(new RegExp(
                    '(?:^|; )' + name.replace(/([.$?*|{}()[]\/+^])/g, '\\$1') + '=([^;]*)'
                ));
                return matches ? decodeURIComponent(matches[1]) : undefined;
            }

            async function updateRating() {
                let ratingValue = document.getElementById('ratingField').value;
                let bookId = document.getElementById('bookId').value;

                const apiToken = '{{ session('api_token') }}'; // Get the token from the session
                await fetchCSRFToken(); // Ensure CSRF token is fetched

                try {
                    const xsrfToken = getCookie('XSRF-TOKEN');
                    const params = new URLSearchParams();
                    params.append('rating', ratingValue);
                    params.append('bookId', bookId);
                    const response = await fetch('{{route('api.updateRating')}}', {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-XSRF-TOKEN': xsrfToken, // Use the fetched token
                            'Authorization': `Bearer ${apiToken}`
                        },
                        body: params.toString(),
                    });
                    if (response.ok) {
                        document.getElementById('ratingDisplay').innerHTML = ratingValue;
                    } else {
                        const errorText = await response.text();
                        console.error('Network response was not ok:', response.status, response.statusText, errorText);
                    }
                } catch (error) {
                    console.error('Update Rating Error:', error);
                }
            }

            // Call the function to update rating
            await updateRating();
        });
    </script>

@endsection
