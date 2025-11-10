const token = localStorage.getItem('emil-web-token');
if (!token) {
    window.location.href = '../../konto/login?redirect=film-søk';
}

// Elements
const input = document.getElementById("searchInput");
const resultsContainer = document.getElementById("results");

// TMDB genre mapping
const genreMap = {
    28: "Action", 12: "Adventure", 16: "Animation", 35: "Comedy", 80: "Crime",
    99: "Documentary", 18: "Drama", 10751: "Family", 14: "Fantasy", 36: "History",
    27: "Horror", 10402: "Music", 9648: "Mystery", 10749: "Romance", 878: "Sci-Fi",
    10770: "TV Movie", 53: "Thriller", 10752: "War", 37: "Western"
};

// Main search function
async function searchMovies() {
    const query = input.value.trim();
    if (!query) {
        resultsContainer.innerHTML = "";
        return;
    }

    try {
        const getHeader = new Headers();
        getHeader.append("Authorization", token);

        const response = await fetch(`https://emil-web-api-production.up.railway.app/movies?query=${encodeURIComponent(query)}`, {
            headers: getHeader
        });

        console.log("Fetch response:", response);

        if (!response.ok) {
            throw new Error(`Server returned ${response.status}`);
        }

        const data = await response.json();
        resultsContainer.innerHTML = "";

        const movies = data.movies.slice(0, 20);

        movies.forEach(movie => {
            const box = document.createElement("div");
            box.classList.add("result-box");

            // Poster
            const img = document.createElement("img");
            img.src = movie.poster_path
                ? `https://image.tmdb.org/t/p/w200${movie.poster_path}`
                : "https://placehold.co/200x300?text=Ingen+Bilde";
            img.alt = movie.title;

            // Title
            const title = document.createElement("div");
            title.classList.add("result-title");
            title.textContent = movie.title;

            // Genres & release date
            const genres = (movie.genre_ids || []).map(id => genreMap[id] || "Unknown").join(", ");
            const info = document.createElement("div");
            info.classList.add("result-info");
            info.textContent = `${movie.release_date || "Unknown"} • ${genres}`;

            // Append elements
            box.appendChild(img);
            box.appendChild(title);
            box.appendChild(info);
            resultsContainer.appendChild(box);
        });

    } catch (err) {
        console.error("Error fetching movies:", err);
        if (resultsContainer) {
            resultsContainer.innerHTML = "<p style='color: red;'>Kunne ikke hente filmer.</p>";
        }
    }
}

// Event listener for Enter key
input.addEventListener("keydown", (event) => {
    if (event.key === "Enter") searchMovies();
});

// Later you can call:
// button.addEventListener("click", searchMovies);
