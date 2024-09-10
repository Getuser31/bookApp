const API_URL = 'https://www.googleapis.com/books/v1/volumes';

function getSearchQueryUrl(Title, language, author = null) {
    if (author) {
        return `${API_URL}?q=intitle:${Title}+inauthor:${author}&langRestrict=${language}`;
    }
    return `${API_URL}?q=intitle:${Title}&langRestrict=${language}`;
}

function handleFetchResponse(response) {
    return response.json();
}

function handleFetchData(data, resultsDivApi) {
    console.log("Data received: ", data);
    resultsDivApi.innerHTML = ''; // Clear previous data
    const books = data.items || [];
    books.forEach(item => {
        let resultItem = createResultItem(item);
        resultsDivApi.appendChild(resultItem);
    });
    resultsDivApi.classList.remove('hidden');
}

function handleError(error) {
    console.error('Error:', error);  // Log any errors
}

function createResultItem(item) {
    let resultItem = document.createElement('div');
    resultItem.classList.add('search-result-item'); // Add a class for CSS styling
    let thumbnailElement = createThumbnailElement(item);
    let contentElement = createContentElement(item);
    if (thumbnailElement) {
        resultItem.appendChild(thumbnailElement);
    }
    resultItem.appendChild(contentElement);
    resultItem.addEventListener('click', () => handleClickResultItem(item));
    return resultItem;
}

function createThumbnailElement(item) {
    if (item.volumeInfo.imageLinks && item.volumeInfo.imageLinks.thumbnail) {
        let thumbnailElement = document.createElement('img');
        thumbnailElement.src = item.volumeInfo.imageLinks.thumbnail;
        thumbnailElement.alt = `Thumbnail for ${item.volumeInfo.title}`;
        thumbnailElement.classList.add('thumbnail');
        return thumbnailElement;
    }
    return null;
}

function createContentElement(item) {
    let contentElement = document.createElement('div');
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
    return contentElement;
}

function handleClickResultItem(item) {
    const book = {
        id: item.id,
        title: item.volumeInfo.title,
        author: item.volumeInfo.authors,
        dateOfPublication: item.volumeInfo.publishedDate,
        genre: item.volumeInfo.categories ? item.volumeInfo.categories : '',
        description: item.volumeInfo.description,
        thumbnail: item.volumeInfo?.imageLinks?.thumbnail ? item.volumeInfo.imageLinks.thumbnail : '',
    };
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    submitBookForm(book, csrfToken);
}

function submitBookForm(book, csrfToken) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/googleBook';
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    const bookInput = document.createElement('input');
    bookInput.type = 'hidden';
    bookInput.name = 'book';
    bookInput.value = JSON.stringify(book);
    form.appendChild(bookInput);
    document.body.appendChild(form);
    form.submit();
}

function formatString(str) {
    return str.replace(/ /g, "+");
}

function initializeBookSearch(GOOGLE_BOOK_SEARCH_ID, SEARCH_API_RESULTS_ID, defaultLanguage, Title, Author = null) {
    return new Promise((resolve, reject) => {
        const resultsDivApi = document.getElementById(SEARCH_API_RESULTS_ID);
        console.log("Search initiated: ", Title);
        if (Title) {
            let url
            if (Author) {
                url = getSearchQueryUrl(formatString(Title), defaultLanguage, formatString(Author));
            } else {
                url = getSearchQueryUrl(formatString(Title), defaultLanguage);
            }
            console.log(url)
            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
                .then(handleFetchResponse)
                .then(data => {
                    handleFetchData(data, resultsDivApi);
                    resolve(); // Resolve after data is handled
                })
                .catch(error => {
                    handleError(error);
                    reject(error); // Reject if there's an error
                });
        } else {
            resultsDivApi.classList.add('hidden');
            resolve(); // Resolve if no title given
        }

        // Close resultsDivApi when clicking outside
        document.addEventListener('click', function (event) {
            const resultsDivApi = document.getElementById(SEARCH_API_RESULTS_ID);
            const searchBar = document.getElementById(GOOGLE_BOOK_SEARCH_ID);
            if (!searchBar.contains(event.target) && !resultsDivApi.contains(event.target)) {
                resultsDivApi.classList.add('hidden');
            }
        });
    });
}

export {initializeBookSearch};
