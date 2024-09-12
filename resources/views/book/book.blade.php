@extends('index')

@vite('resources/css/book/book.css')
@section('content')

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">{!! $book->title !!}</h1>

        <div class="flex">
            <div class="w-96 h-64 mr-8">
                @if (file_exists(public_path($book->picture)))
                    <img src="{{ asset('/'.$book->picture) }}" alt="book picture"
                         class="object-contain h-full w-full">
                @else
                    <img src="{{$book->picture}}" alt="book picture"
                         class="object-contain h-full w-full">
                @endif
            </div>

            <div>
                <ul class="list-none">
                    <li class="mb-2">
                        <span class="font-semibold">Date de publication:</span> {{ $book->date_of_publication}}
                    </li>
                    <li class="mb-2">
                        <span class="font-semibold">Auteur:</span> {{ $book->author->name }}
                    </li>
                    <li class="mb-2">
                        <span class="font-semibold">Synopsis:</span>
                        <p>{!! $book->description !!}</p>
                    </li>
                    <li class="mb-2">
                        <span class="font-semibold">Genre:</span> {{collect($book->genres)->pluck('name')->implode(' / ')}}
                    </li>
                    @if($book->collection)
                        <li class="mb-2">
                            <span class="font-semibold">Collection:</span> {{ $book->collection->name }}
                        </li>
                    @endif

                    @if($belongToUser)
                        <li class="mb-2">
                            <span class="font-semibold">Progression:</span>
                            <span id="progressionDisplay">{{$progression}}</span> %
                            <form id="progressionForm" class="inline-block ml-4">
                                <meta name="csrf-token" content="{{ csrf_token() }}">
                                <label for="progressionField" class="sr-only">Set Progression:</label>
                                <input type="number" id="progressionField" name="progressionField" min="0" max="100"
                                       class="w-16 border border-gray-300 rounded-md px-2 py-1 text-sm">
                                <input type="hidden" id="bookId" value="{{$book->id}}">
                                <button type="submit"
                                        class="ml-2 px-3 py-1 bg-blue-500 hover:bg-blue-700 text-white text-sm rounded-md">
                                    Update
                                </button>
                            </form>
                        </li>
                        <li class="mb-2">
                            <span hidden id="ratingDisplay">{{ $rating ?? '' }}</span>
                            <form id="ratingForm" class="inline-block">
                                <meta name="csrf-token-rating" id='csrf-token-rating' content="{{csrf_token()}}">
                                <div id="starContainer" data-current-rating="{{ $rating ?? 0 }}" class="flex">
                                    @for ($i = 1; $i <= 10; $i++)
                                        <span class="star cursor-pointer text-2xl" data-value="{{ $i }}">&#9733;</span>
                                    @endfor
                                </div>
                                <input type="hidden" id="ratingField" name="ratingField" min="0" max="10"
                                       value="{{ $rating ?? 0 }}">
                                <input type="hidden" id="bookId" value="{{$book->id}}">
                            </form>
                        </li>
                        <li class="mb-2">
                            <form id="favoriteForm" class="flex items-center">
                                <meta name="csrf-token-rating" id='csrf-token-rating' content="{{csrf_token()}}">
                                <input type="checkbox" name="favorite" id="favorite" {{$favorite ? 'checked' : ''}}
                                class="mr-2">
                                <label for="favorite" class="font-semibold">Favorite</label>
                            </form>
                        </li>
                    @else
                        <a href="{{route('book.addBookPost', ['id' => $book->id])}}"
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md inline-block">
                            Add book
                        </a>
                    @endif
                </ul>
            </div>
        </div>
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
            const stars = document.querySelectorAll('.star');
            const ratingDisplay = document.getElementById('ratingDisplay');
            const favoriteButton = document.getElementById('favorite');
            const ratingField = document.getElementById('ratingField');
            const bookIdField = document.getElementById('bookId');
            const currentRating = parseInt(document.getElementById('starContainer').getAttribute('data-current-rating'));

            // Function to set star selection based on rating
            function setStarSelection(rating) {
                stars.forEach(star => star.classList.remove('selected'));
                for (let i = 0; i < rating; i++) {
                    stars[i].classList.add('selected');
                }
            }

            // Initialize stars based on current rating
            setStarSelection(currentRating);
            ratingField.value = currentRating;

            stars.forEach(star => {
                star.addEventListener('click', async function () {
                    const rating = this.getAttribute('data-value');
                    ratingField.value = rating;

                    // Update the display
                    ratingDisplay.textContent = rating;

                    // Reset and select stars
                    stars.forEach(s => s.classList.remove('selected'));
                    for (let i = 0; i < rating; i++) {
                        stars[i].classList.add('selected');
                    }
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
