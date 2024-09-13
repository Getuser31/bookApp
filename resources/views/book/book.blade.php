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
                        <span
                            class="font-semibold">Genre:</span> {{collect($book->genres)->pluck('name')->implode(' / ')}}
                    </li>
                    @if($book->collection)
                        <li class="mb-2">
                            <span class="font-semibold">Collection:</span> {{ $book->collection->name }}
                        </li>
                    @endif

                    @if($belongToUser)
                        <li class="mb-2">
                            <span class="font-semibold">Progression:</span>
                            <form id="progressionForm" class="inline-block ml-4">
                                <meta name="csrf-token" content="{{ csrf_token() }}">
                                <label for="progressionField" class="sr-only">Set Progression:</label>
                                <select id="progressionField" name="progressionField"
                                        class="w-16 border border-gray-300 rounded-md px-2 py-1 text-sm">
                                    @for ($i = 0; $i <= 100; $i+=10)
                                        <option
                                            value="{{ $i }}" {{ $progression == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                <input type="hidden" id="bookId" value="{{$book->id}}">
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
                @if($notes->isNotEmpty())
                    <div class="mt-4">
                        <h3 class="text-lg font-semibold mb-2">Notes:</h3>
                        <div class="space-y-2">
                            @foreach($notes as $note)
                                <div class="p-3 bg-gray-100 rounded-md">
                                    <p class="text-sm text-gray-600">
                                        {{ $note->created_at->format('d/m/Y') }}
                                    </p>
                                    <p class="mt-2">{{ $note->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="mb-2">
                    <button id="noteButton"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold mt-8 py-2 px-4 rounded-md">
                        Add Note
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div id="addNoteModal" tabindex="-1" aria-hidden="true"
         class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button"
                        class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                        data-modal-hide="addNoteModal">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                         xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                              clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="py-6 px-6 lg:px-8">
                    <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Add a Note</h3>
                    <form id="addNoteForm" class="space-y-6" action="{{ route('notes.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <div>
                            <label for="note_content"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Your
                                Note</label>
                            <textarea id="note_content" name="content" rows="4"
                                      class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                      placeholder="Write your note here..."></textarea>
                        </div>
                        <button type="submit"
                                class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Add Note
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let progressionField = document.getElementById('progressionField');
        progressionField.addEventListener('change', async function (e) {
            e.preventDefault();

            // Retrieve the CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const bookId = document.getElementById('bookId').value;
            const progressionValue = progressionField.value;

            const params = new URLSearchParams();
            params.append('progression', progressionValue);
            params.append('bookId', bookId);

            const response = await makePostRequest('{{route('api.updateProgression')}}', params);
            if (response && response.ok) {
               console.log('progression updated...')
            }


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

    <script> //Call the modal to add notes
        // Add event listener to the button to open the modal
        const noteButton = document.getElementById('noteButton');
        const modal = document.getElementById('addNoteModal');
        const closeButton = modal.querySelector('button[data-modal-hide="addNoteModal"]');

        noteButton.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });

        closeButton.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    </script>

@endsection
