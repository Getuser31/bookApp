import '../css/app.css'
import { fetchCSRFToken, getCookie, makePostRequest } from './ajax-helpers';
import { initializeBookSearch } from './searchBook';
import { handleEnterKey} from './searchHandlers';

// Optionally, attach these functions to the window object for global access
window.fetchCSRFToken = fetchCSRFToken;
window.getCookie = getCookie;
window.makePostRequest = makePostRequest;
window.initializeBookSearch = initializeBookSearch;

document.addEventListener('keydown', handleEnterKey);

