<?php
$appId = 'DYMYCN7BKE';
$searchKey = 'ee09b2154230972eb010fdd8709e49fc';
$indexName = 'movies';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies Web Search</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="page">
        <div class="mobile-overlay" id="mobileOverlay"></div>
        <aside class="sidebar">
            <h1>Newflix</h1>
            <div id="stats" class="sidebar-stats"></div>
            <div id="clear-filters" class="sidebar-clear"></div>
            <div class="facet-group">
                <h3>Genre</h3>
                <div id="genre-list"></div>
            </div>
            <div class="facet-group">
                <h3>Release Year</h3>
                <div id="year-list"></div>
            </div>
        </aside>

        <div class="main-content">
            <header>
                <button class="mobile-menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <h1>Movies</h1>
                <p>Search through thousands of movies. Find your next favorite by filtering by genre and release year.</p>
            </header>

            <div class="content-wrapper">
                <div class="search-section">
                    <div id="searchbox"></div>
                </div>

                <h2 class="section-title">Results</h2>
                <div class="hits" id="hits"></div>
                <div id="pagination"></div>
            </div>
        </div>
        <!-- Movie Details Modal -->
        <div id="movieModal" class="modal">
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <div class="modal-body">
                    <img id="modalPoster" src="" alt="Movie poster" class="modal-poster">
                    <div class="modal-details">
                        <h1 id="modalTitle" class="modal-title"></h1>
                        <div id="modalMeta" class="modal-meta"></div>
                        <div id="modalOverview" class="modal-overview"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/algoliasearch@4/dist/algoliasearch-lite.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/instantsearch.js@4"></script>
    <script>
        // Mobile menu toggle functionality
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');

        const toggleMenu = () => {
            sidebar.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
        };

        const closeMenu = () => {
            sidebar.classList.remove('active');
            mobileOverlay.classList.remove('active');
        };

        menuToggle.addEventListener('click', toggleMenu);

        // Close sidebar when clicking on the overlay
        mobileOverlay.addEventListener('click', closeMenu);

        // Close sidebar when clicking on the main content (on mobile)
        document.querySelector('.main-content').addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && !e.target.closest('.mobile-menu-toggle')) {
                closeMenu();
            }
        });

        // Close sidebar on window resize if window is larger than mobile breakpoint
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeMenu();
            }
        });

        const searchClient = algoliasearch('<?php echo $appId; ?>', '<?php echo $searchKey; ?>');

        const search = instantsearch({
            indexName: '<?php echo $indexName; ?>',
            searchClient,
            routing: false,
        });

        const hitTemplate = `
            <article class="hit-card" data-title="{{title}}" data-poster="{{poster_url}}" data-genre="{{genre}}" data-year="{{release_date}}" data-rating="{{vote_average}}" data-overview="{{overview}}">
                <div class="hit-card-image-container">
                    <img src="{{poster_url}}" alt="{{title}} poster" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                </div>
                <div class="hit-body">
                    <h2 class="hit-title">{{#helpers.highlight}}{ "attribute": "title" }{{/helpers.highlight}}</h2>
                    <p class="hit-overview">{{#helpers.snippet}}{ "attribute": "overview" }{{/helpers.snippet}}</p>
                    <div class="hit-card-meta">
                        {{#genre}}<span class="hit-card-badge"><strong>Genre:</strong> {{genre}}</span>{{/genre}}
                        {{#release_date}}<span class="hit-card-badge"><strong>Release Year:</strong> {{release_date}}</span>{{/release_date}}
                        <span class="hit-card-badge"><strong>Rating:</strong> {{vote_average}}/10</span>
                    </div>
                </div>
            </article>
        `;

        search.addWidgets([
            instantsearch.widgets.searchBox({
                container: '#searchbox',
                placeholder: 'Search movies by title, overview, or genre',
                showSubmit: false,
                showReset: true,
                showLoadingIndicator: true,
                cssClasses: {
                    root: 'ais-SearchBox',
                },
            }),
            instantsearch.widgets.stats({ container: '#stats' }),
            instantsearch.widgets.clearRefinements({ container: '#clear-filters' }),
            instantsearch.widgets.refinementList({
                container: '#genre-list',
                attribute: 'genre',
                searchable: true,
                showMore: true,
                sortBy: ['count:desc', 'name:asc'],
                limit: 10,
                showMoreLimit: 20,
            }),
            instantsearch.widgets.menuSelect({
                container: '#year-list',
                attribute: 'year',
                limit: 12,
                sortBy: ['name:desc'],
                templates: {
                    defaultOption: 'All years',
                },
            }),
            instantsearch.widgets.hits({
                container: '#hits',
                templates: {
                    item: hitTemplate,
                    empty: '<div>No movies found. Try a different query.</div>',
                },
            }),
            instantsearch.widgets.pagination({
                container: '#pagination',
                padding: 2,
                showFirst: true,
                showLast: true,
                showPrevious: true,
                showNext: true,
                scrollTo: '#hits',
            }),
        ]);

        search.start();

        // Modal functionality
        const modal = document.getElementById('movieModal');
        const closeBtn = document.querySelector('.modal-close');

        // Close modal on X click
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Close modal on outside click
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Open modal on card click
        document.addEventListener('click', function(event) {
            const card = event.target.closest('.hit-card');
            if (card) {
                const title = card.getAttribute('data-title');
                const poster = card.getAttribute('data-poster');
                const genre = card.getAttribute('data-genre') || 'N/A';
                const year = card.getAttribute('data-year') || 'N/A';
                const rating = card.getAttribute('data-rating') || 'N/A';
                const overview = card.getAttribute('data-overview') || 'No description available.';

                document.getElementById('modalPoster').src = poster || 'https://via.placeholder.com/500x750?text=No+Image';
                document.getElementById('modalTitle').textContent = title;
                document.getElementById('modalMeta').innerHTML = `
                    <p><strong>Genre:</strong> ${genre}</p>
                    <p><strong>Release Year:</strong> ${year}</p>
                    <p><strong>Rating:</strong> ${rating}/10</p>
                `;
                document.getElementById('modalOverview').innerHTML = `<p>${overview}</p>`;
                modal.style.display = 'block';
            }
        });

        document.addEventListener('click', function(event) {
            const facetSearchButton = event.target.closest('.facet-group .ais-SearchBox-submit');
            if (facetSearchButton) {
                event.stopPropagation();
            }
        });
    </script>
</body>
</html>
