document.addEventListener('DOMContentLoaded', () => {
    //Suchoptionen
    const suggestions = [
        'Home',
        'Über Uns',
        'Produkte',
        'Kontakt',
        'Warenkorb',
        'Wetter',
        'Impressum',
        'Spezial',
    ];

    const searchInput = document.getElementById('search');
    const suggestionList = document.getElementById('suggestions');

    // Abgleichen ob Sucheingabe und Suchoptionen einstimmen
    if (searchInput && suggestionList) {
        searchInput.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            suggestionList.innerHTML = '';

            if (value) {
                const filteredSuggestions = suggestions.filter(item => item.toLowerCase().includes(value));
                filteredSuggestions.forEach(suggestion => {
                    const li = document.createElement('li');
                    li.textContent = suggestion;
                    li.onclick = () => {
                        searchInput.value = suggestion;
                        suggestionList.innerHTML = '';
                        suggestionList.style.display = 'none';
                    };
                    suggestionList.appendChild(li);
                });
                suggestionList.style.display = filteredSuggestions.length ? 'block' : 'none';
            } else {
                suggestionList.style.display = 'none';
            }
        });
    }

    //Suchleiste
    const searchForm = document.getElementById('search-form');
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const query = searchInput.value.toLowerCase();

            switch (query) {
                case 'home':
                    window.location.href = 'index.html';
                    break;
                case 'about':
                    window.location.href = 'about.html';
                    break;
                case 'produkte':
                    window.location.href = 'services.html';
                    break;
                case 'kontakt':
                    window.location.href = 'contact.html';
                    break;
                case 'warenkorb':
                    window.location.href = 'warenkorb.html';
                    break;
                case 'impressum':
                    window.location.href = 'impressum.html';
                    break;
                case 'spezial':
                    window.location.href = 'spezial.html';
                    break;
                case 'wetter':
                    window.location.href = 'wetter.html';
                    break;
                default:
                    alert('Seite nicht gefunden');
                    searchInput.value = '';
            }
        });
    }

    // Carousel code
    const carousel = document.querySelector('.carousel');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let currentIndex = 0;
    const itemsToShow = 3;
    const carouselItem = document.querySelector('.carousel-item');
    const totalItems = carouselItem ? document.querySelectorAll('.carousel-item').length : 0;
    const itemWidth = carouselItem ? carouselItem.clientWidth + 10 : 0;
    
    function updateCarousel() {
        const offset = currentIndex * -itemWidth;
        if (carousel) {
            carousel.style.transform = `translateX(${offset}px)`;
        }
    }

    /* Carousel Knöpfe */
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex -= 1;  
                updateCarousel();
            }
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (currentIndex < totalItems - itemsToShow) {
                currentIndex += 1;
                updateCarousel();
            }
        });
    }

    window.addEventListener('resize', () => {
        updateCarousel();
    });

    // Bewertungen "Mehr Anzeigen" Knopf
    const toggleBtn = document.getElementById("toggleBtn");
    if (toggleBtn) {
        toggleBtn.addEventListener("click", function() {
            const hiddenTestimonials = document.querySelectorAll(".versteckte-Bewertungen");
            const isHidden = hiddenTestimonials.length && (hiddenTestimonials[0].style.display === "none" || hiddenTestimonials[0].style.display === "");

            hiddenTestimonials.forEach(function(testimonial) {
                testimonial.style.display = isHidden ? "block" : "none";
            });
            this.textContent = isHidden ? "Weniger" : "More";
        });
    }
});

// Cookies
document.addEventListener('DOMContentLoaded', (event) => {
    const cookieConsentBanner = document.getElementById('cookieConsentBanner');
    const acceptButton = document.getElementById('acceptCookies');
    const rejectButton = document.getElementById('rejectCookies');

    if (cookieConsentBanner && acceptButton && rejectButton) {
        if (!document.cookie.includes('cookiesAccepted=true') && !document.cookie.includes('cookiesRejected=true')) {
            cookieConsentBanner.style.display = 'block';
        }

        // Accept cookies
        acceptButton.addEventListener('click', () => {
            document.cookie = "cookiesAccepted=true; max-age=31536000; path=/";
            cookieConsentBanner.style.display = 'none';
        });

        // Reject cookies
        rejectButton.addEventListener('click', () => {
            document.cookie = "cookiesRejected=true; max-age=31536000; path=/";
            cookieConsentBanner.style.display = 'none';
        });
    }
});
