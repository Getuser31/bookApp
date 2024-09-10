const API_URL = 'https://www.googleapis.com/books/v1/volumes';

let currentPage = 1; // Initialize current page as a global variable

function getSearchQueryUrl(Title, language, author = null, startIndex = 0) {
    let query = `${API_URL}?q=intitle:${Title}&langRestrict=${language}&startIndex=${startIndex}&maxResults=10`;
    if (author) {
        query = `${API_URL}?q=intitle:${Title}+inauthor:${author}&langRestrict=${language}&startIndex=${startIndex}&maxResults=10`;
    }
    return query;
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
    if (data.totalItems > 10) {
        let nbOfPages = Math.ceil(data.totalItems / 10);
        window.nbOfPages = nbOfPages;
        renderPagination(nbOfPages, currentPage); // Use the global current page
    }
    resultsDivApi.classList.remove('hidden');
}

function renderPagination(nbOfPages, currentPage) {
    let pagination = document.getElementById('pagination');
    if (!pagination) {
        let container = document.createElement('div');
        container.className = 'max-w-2xl mx-auto';
        document.body.appendChild(container);

        let contentDiv = document.createElement('div');
        contentDiv.id = 'content';
        contentDiv.className = 'bg-white p-6 rounded-lg shadow-lg mb-4';
        container.appendChild(contentDiv);

        let paginationUl = document.createElement('ul');
        paginationUl.className = 'pagination flex justify-center space-x-2';
        paginationUl.id = 'pagination';
        container.appendChild(paginationUl);

        pagination = paginationUl;
    } else {
        pagination.innerHTML = ''; // Clear existing pagination elements
    }

    if (nbOfPages <= 7) {
        for (let i = 1; i <= nbOfPages; i++) {
            addPageItem(pagination, i, currentPage);
        }
    } else {
        if (currentPage <= 4) {
            for (let i = 1; i <= 5; i++) {
                addPageItem(pagination, i, currentPage);
            }
            addEllipsis(pagination);
            addPageItem(pagination, nbOfPages, currentPage);
        } else if (currentPage > nbOfPages - 4) {
            addPageItem(pagination, 1, currentPage);
            addEllipsis(pagination);
            for (let i = nbOfPages - 4; i <= nbOfPages; i++) {
                addPageItem(pagination, i, currentPage);
            }
        } else {
            addPageItem(pagination, 1, currentPage);
            addEllipsis(pagination);
            for (let i = currentPage - 1; i <= currentPage + 1; i++) {
                addPageItem(pagination, i, currentPage);
            }
            addEllipsis(pagination);
            addPageItem(pagination, nbOfPages, currentPage);
        }
    }
}

function addPageItem(pagination, page, currentPage) {
    let li = document.createElement('li');
    li.className = 'cursor-pointer inline-block';
    if (page === currentPage) {
        li.classList.add('font-bold', 'text-red-500');
    }
    let anchor = document.createElement('a');
    anchor.href = '#';
    anchor.innerText = page;
    anchor.className = 'block px-3 py-1 rounded-lg hover:bg-gray-200';
    li.appendChild(anchor);

    li.addEventListener('click', function () {
        currentPage = page;
        loadPageContent(currentPage);
        renderPagination(window.nbOfPages, currentPage);
    });
    pagination.appendChild(li);
}

function addEllipsis(pagination) {
    let li = document.createElement('li');
    li.className = 'inline-block';
    li.innerText = '...';
    pagination.appendChild(li);
}

function loadPageContent(page) {
    currentPage = page; // Update the global current page
    const resultsDivApi = document.getElementById(window.SEARCH_API_RESULTS_ID);
    const startIndex = (page - 1) * 10;

    let url = getSearchQueryUrl(
        formatString(window.Title),
        window.defaultLanguage,
        window.Author ? formatString(window.Author) : null,
        startIndex
    );

    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then(handleFetchResponse)
        .then(data => {
            handleFetchData(data, resultsDivApi);
        })
        .catch(handleError);
}

function handleError(error) {
    console.error('Error:', error); // Log any errors
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
    window.GOOGLE_BOOK_SEARCH_ID = GOOGLE_BOOK_SEARCH_ID;
    window.SEARCH_API_RESULTS_ID = SEARCH_API_RESULTS_ID;
    window.defaultLanguage = defaultLanguage;
    window.Title = Title;
    window.Author = Author;

    return new Promise((resolve, reject) => {
        const resultsDivApi = document.getElementById(SEARCH_API_RESULTS_ID);
        console.log("Search initiated: ", Title);
        if (Title) {
            let url = getSearchQueryUrl(formatString(Title), defaultLanguage, Author ? formatString(Author) : null);
            console.log(url);
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

export { initializeBookSearch };
