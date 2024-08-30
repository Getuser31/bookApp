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
                <li><b>Rating:</b>
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
                <li>
                    <form id="favoriteForm">
                        <meta name="csrf-token-rating" id='csrf-token-rating' content="{{csrf_token()}}">
                        <label for="favorite">Favorite</label>
                        <input type="checkbox" name="favorite" id="favorite" {{$favorite ? 'checked' : ''}}>
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
      document.addEventListener('DOMContentLoaded', (event) => {
        const ratingForm = document.getElementById('ratingForm');
        const favoriteButton = document.getElementById('favorite');
        const ratingField = document.getElementById('ratingField');
        const bookIdField = document.getElementById('bookId');

        ratingForm.addEventListener('submit', async function (e) {
          e.preventDefault();
          const ratingValue = ratingField.value;
          const bookId = bookIdField.value;

          const params = new URLSearchParams();
          params.append('rating', ratingValue);
          params.append('bookId', bookId);

          const response = await makePostRequest('{{route('api.updateRating')}}', params);
          if (response && response.ok) {
            document.getElementById('ratingDisplay').innerHTML = ratingValue;
          }
        });

        favoriteButton.addEventListener('click', async function (e) {
          const favorite = favoriteButton.checked;
          const bookId = bookIdField.value;

          const params = new URLSearchParams();
          params.append('favorite', favorite);
          params.append('bookId', bookId);

          const response = await makePostRequest('{{route('api.updateFavorite')}}', params);
          if (response && response.ok) {
            favoriteButton.checked = favorite;
          }
        });
      });
    </script>

@endsection
