(function () {
    const input = document.getElementById('met-vejr-search');
    const search = document.getElementById('met-vejr-search-button');
    const status = document.getElementById('met-vejr-search-status');
    const results = document.getElementById('met-vejr-search-results');
    const cities = document.getElementById('met-vejr-cities');

    async function findCities() {
        if (search.disabled) return;
        const query = input.value.trim();
        results.replaceChildren();
        if (query.length < 2) {
            status.textContent = 'Enter at least two characters.';
            return;
        }
        status.textContent = 'Searching…';
        search.disabled = true;
        try {
            const response = await fetch(window.ajaxurl, {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'met_vejr_search_cities',
                    _ajax_nonce: search.dataset.nonce,
                    query
                })
            });
            const payload = await response.json();
            if (!payload.success) {
                throw new Error(typeof payload.data === 'string' ? payload.data : 'City search is temporarily unavailable.');
            }
            status.textContent = payload.data.length ? 'Select a city to add it.' : 'No matching cities found.';
            for (const match of payload.data) {
                const item = document.createElement('li');
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'button';
                button.textContent = match.label;
                button.addEventListener('click', function () {
                    const lines = cities.value.split(/\r?\n/).map(line => line.trim()).filter(Boolean);
                    const coordinates = line => line.split('|').slice(1).map(Number).join(',');
                    if (lines.some(line => coordinates(line) === coordinates(match.value))) {
                        status.textContent = 'This location is already in the list.';
                    } else if (lines.length >= 10) {
                        status.textContent = 'Remove a city before adding another. The limit is 10.';
                    } else {
                        cities.value = [...lines, match.value].join('\n');
                        status.textContent = match.label + ' added. Save Changes to apply.';
                    }
                });
                item.append(button);
                results.append(item);
            }
        } catch (error) {
            status.textContent = error.message || 'City search is temporarily unavailable.';
        } finally {
            search.disabled = false;
        }
    }

    search.addEventListener('click', findCities);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            findCities();
        }
    });
})();
