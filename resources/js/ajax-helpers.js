// public/js/ajax-helpers.js

async function fetchCSRFToken() {
    try {
        const response = await fetch('http://localhost:8000/sanctum/csrf-cookie', {
            credentials: 'include',
        });
        if (!response.ok) {
            throw new Error(`Network response was not ok (${response.status})`);
        }
        return response.headers.get('X-XSRF-TOKEN');
    } catch (error) {
        console.error('Fetch CSRF Token Error:', error);
        throw error;
    }
}

function getCookie(name) {
    const matches = document.cookie.match(new RegExp(
        '(?:^|; )' + name.replace(/([.$?*|{}()[]\/+^])/g, '\\$1') + '=([^;]*)'
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

async function makePostRequest(url, params) {
    const apiToken = document.querySelector('meta[name="api-token"]').getAttribute('content');
    await fetchCSRFToken();
    try {
        const xsrfToken = getCookie('XSRF-TOKEN');
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-XSRF-TOKEN': xsrfToken,
                'Authorization': `Bearer ${apiToken}`,
            },
            body: params.toString(),
        });
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Network response was not ok:', response.status, response.statusText, errorText);
        }
        return response;
    } catch (error) {
        console.error('Request Error:', error);
    }
}

export { fetchCSRFToken, getCookie, makePostRequest };
