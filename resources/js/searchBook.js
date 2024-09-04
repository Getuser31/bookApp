let searchInput = document.getElementById('GoogleBookSearch')
document.getElementById('GoogleBookSearch').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();  // To ensure the form isn't submitted
        const value = e.target.value;
        const resultsDivApi = document.getElementById('searchApiResults');
        console.log("Search initiated: ", value);

        if (value) {
            let url = "https://www.googleapis.com/books/v1/volumes?q=" + value + "&langRestrict=fr";

            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    console.log("Data received: ", data);
                    resultsDivApi.innerHTML = ''; // Clear previous data

                    const books = data.items || [];
                    books.forEach(item => {
                        let resultItem = document.createElement('div');
                        resultItem.classList.add('search-result-item'); // Add a class for CSS styling

                        let thumbnailElement, contentElement;

                        if (item.volumeInfo.imageLinks && item.volumeInfo.imageLinks.thumbnail) {
                            thumbnailElement = document.createElement('img');
                            thumbnailElement.src = item.volumeInfo.imageLinks.thumbnail;
                            thumbnailElement.alt = `Thumbnail for ${item.volumeInfo.title}`;
                            thumbnailElement.classList.add('thumbnail');
                        }

                        contentElement = document.createElement('div');
                        contentElement.classList.add('content');

                        let titleElement = document.createElement('p');
                        titleElement.classList.add('title');
                        titleElement.textContent = `Title: ${item.volumeInfo.title}`;
                        contentElement.appendChild(titleElement);

                        let authorElement = document.createElement('p');
                        authorElement.textContent = `Author: ${item.volumeInfo.authors ? item.volumeInfo.authors.join(', ') : 'N/A'}`;
                        contentElement.appendChild(authorElement);

                        let dateElement = document.createElement('p');
                        dateElement.textContent = `Date of Publication: ${item.volumeInfo.publishedDate || 'N/A'}`;
                        contentElement.appendChild(dateElement);

                        let genreElement = document.createElement('p');
                        genreElement.textContent = `Genre: ${item.volumeInfo.categories ? item.volumeInfo.categories.join(', ') : 'N/A'}`;
                        contentElement.appendChild(genreElement);

                        if (item.volumeInfo.description) {
                            let descriptionElement = document.createElement('p');
                            descriptionElement.textContent = `Description: ${item.volumeInfo.description.length > 250
                                ? item.volumeInfo.description.substring(0, 247) + '...'
                                : item.volumeInfo.description || 'N/A'}`;
                            contentElement.appendChild(descriptionElement);
                        }

// Append thumbnail and content to resultItem
                        if (thumbnailElement) {
                            resultItem.appendChild(thumbnailElement);
                        }
                        resultItem.appendChild(contentElement);

// Add click event handler for result item
                        resultItem.addEventListener('click', function() {
                            const book = {
                                id: item.id,
                                title: item.volumeInfo.title,
                                author: item.volumeInfo.authors,
                                dateOfPublication: item.volumeInfo.publishedDate,
                                genre: item.volumeInfo.categories ? item.volumeInfo.categories : '',
                                description: item.volumeInfo.description,
                                thumbnail: item.volumeInfo?.imageLinks?.thumbnail ? item.volumeInfo.imageLinks.thumbnail : '',
                            };

                            // Create a hidden form for POST submission
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '/googleBook';

                            // Add CSRF token input to the form
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = csrfToken;
                            form.appendChild(csrfInput);

                            // Clear any previous book data inputs
                            form.querySelectorAll('input[name="book"]').forEach(e => e.remove());

                            // Add JSON data as a single hidden input
                            const bookInput = document.createElement('input');
                            bookInput.type = 'hidden';
                            bookInput.name = 'book';
                            bookInput.value = JSON.stringify(book);
                            form.appendChild(bookInput);

                            // Append the form to the document body
                            document.body.appendChild(form);

                            // Submit the form
                            form.submit();
                        });

// Append the result item to the results div
                        resultsDivApi.appendChild(resultItem);
                    });

                    // Make sure the results are visible
                    resultsDivApi.classList.remove('hidden');
                    console.log("Results appended to the DOM.");
                })
                .catch(error => {
                    console.error('Error:', error);  // Log any errors
                });
        } else {
            resultsDivApi.classList.add('hidden');
        }
    }
});

// Close resultsDivApi when clicking outside
document.addEventListener('click', function(event) {
    const resultsDivApi = document.getElementById('searchApiResults');
    const searchBar = document.getElementById('GoogleBookSearch');

    if (!searchBar.contains(event.target) && !resultsDivApi.contains(event.target)) {
        resultsDivApi.classList.add('hidden');
    }
});
