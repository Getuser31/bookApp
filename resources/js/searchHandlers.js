// eventHandlers.js
import { initializeBookSearch } from './searchBook';

export async function handleEnterKey(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        let searchBox = document.getElementById('GoogleBookSearch');

        let title = document.getElementById('GoogleBookSearch').value;
        if (!title || title.trim() === '' || document.activeElement !== searchBox){
            return;
        }
        try {
            await initializeBookSearch('GoogleBookSearch', 'searchApiResults', window.defaultLanguage, title);
            createButton();
        } catch (error) {
            console.error('Error initializing book search:', error);
        }
    }
}

export function createButton() {
    let moreResultsButton = document.createElement('button');
    moreResultsButton.textContent = 'More results';
    moreResultsButton.classList.add('more-results-button');
    moreResultsButton.id = 'moreResultsButton';

    moreResultsButton.addEventListener('click', function () {
        window.location.href = "http://localhost:8000/addBook"
    });

    document.getElementById('searchApiResults').appendChild(moreResultsButton);
}
