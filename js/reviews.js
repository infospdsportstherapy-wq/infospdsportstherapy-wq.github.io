// Fallback reviews data (extracted from database.sql)
// Use this if PHP backend is not available
const fallbackReviews = [
    {
        full_name: 'Matthew Jacobs',
        activity: 'Hockey Player',
        review: 'I play league hockey and after a few intense weekends my legs were constantly tight. Brandon came to me, explained everything during the session and I felt a huge difference before my next match. Highly recommended.',
        rating: 5
    },
    {
        full_name: 'Sarah Williams',
        activity: 'Distance Runner',
        review: 'As a marathon runner I constantly struggle with tight calves and hamstrings. The treatment was professional and tailored to exactly what I needed. Recovery after long runs has definitely improved.',
        rating: 5
    },
    {
        full_name: 'Jason Muller',
        activity: 'Competitive Paintball Player',
        review: 'I compete in speedball paintball and spend entire weekends diving, sprinting and crawling. My shoulders and lower back were constantly sore. After a sports massage I moved much better and recovered much faster.',
        rating: 4
    },
    {
        full_name: 'Nicole Adams',
        activity: 'CrossFit Athlete',
        review: 'Excellent mobile service. Brandon arrived on time, assessed my movement first and focused on the areas that actually needed treatment instead of giving a generic massage. Very knowledgeable.',
        rating: 5
    },
    {
        full_name: 'Daniel van Wyk',
        activity: 'Road Cyclist',
        review: 'I cycle several hundred kilometres every month and developed tight hips and lower back pain. The treatment provided some relief and improved my mobility somewhat, though I expected more significant results. It was decent but the pricing felt a bit high for the service provided.',
        rating: 3
    },
    {
        full_name: 'Bianca Ferreira',
        activity: 'Gym Enthusiast',
        review: 'I have had sports massages before but this experience felt much more personalised. Everything was explained clearly and the treatment focused on my specific problem areas.',
        rating: 5
    },
    {
        full_name: 'Kyle Petersen',
        activity: 'Rugby Player',
        review: 'As a rugby player recovery is just as important as training. The deep tissue treatment reduced soreness after matches and helped me recover much quicker during the season.',
        rating: 5
    },
    {
        full_name: 'Megan Ross',
        activity: 'Triathlete',
        review: 'I work in an office during the week while training for triathlons. The combination left my neck and back incredibly tight. Brandon did address the main issues, though the treatment could have been more tailored to my specific needs. Overall helpful but felt somewhat rushed during the session.',
        rating: 3
    },
    {
        full_name: 'Andrew Botha',
        activity: 'Trail Runner',
        review: 'Professional, friendly and extremely convenient. Having treatment at home saved me time and the session focused on improving movement instead of simply relaxing the muscles.',
        rating: 5
    },
    {
        full_name: 'Emma de Villiers',
        activity: 'Olympic Weightlifter',
        review: 'I compete in Olympic weightlifting and often struggle with shoulder and hip mobility. Regular maintenance sessions have noticeably improved my lifting positions and reduced post-training stiffness.',
        rating: 4
    }
];

// Track current review index
let currentReviewIndex = 0;
let allReviews = [];

// Create review card from static data
function createReviewCardFromData(review) {
    const card = document.createElement('div');
    card.className = 'testimonial-card';
    
    // Generate stars based on rating (default to 5 if not provided)
    const rating = review.rating || 5;
    let starsHTML = '<div class="stars">';
    for (let i = 0; i < rating; i++) {
        starsHTML += '<i class="fas fa-star"></i>';
    }
    starsHTML += '</div>';

    const reviewText = review.review || 'Great service!';
    const shortText = reviewText.length > 200 ? reviewText.substring(0, 200) + '...' : reviewText;

    card.innerHTML = `
        ${starsHTML}
        <p>"${shortText}"</p>
        <h4>- ${review.full_name}</h4>
        <small style="color: var(--text-light); display: block; margin-top: 10px;">${review.activity}</small>
    `;

    return card;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('reviewsCarousel');
    
    if (carousel) {
        // Load all reviews initially
        loadAllReviews();
    }
});

// Function to load all reviews from the database
function loadAllReviews() {
    const carousel = document.getElementById('reviewsCarousel');
    if (!carousel) return;

    // Try to load from API first - request all reviews without limit
    fetch('api/get_reviews.php')
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data.length > 0) {
                allReviews = result.data;
                console.log('Loaded ' + allReviews.length + ' reviews from API');
                displayAllReviews();
                startAutoScroll();
            } else {
                // Fallback to static data
                allReviews = fallbackReviews;
                console.log('Using fallback reviews: ' + allReviews.length + ' reviews');
                displayAllReviews();
                startAutoScroll();
            }
        })
        .catch(error => {
            console.log('API not available, using local reviews:', error);
            // Fallback to static data
            allReviews = fallbackReviews;
            console.log('Using fallback reviews: ' + allReviews.length + ' reviews');
            displayAllReviews();
            startAutoScroll();
        });
}

// Display all reviews in the carousel
function displayAllReviews() {
    const carousel = document.getElementById('reviewsCarousel');
    if (!carousel || allReviews.length === 0) return;

    carousel.innerHTML = '';
    allReviews.forEach(review => {
        const card = createReviewCardFromData(review);
        carousel.appendChild(card);
    });
}

// Start the auto-scroll loop
function startAutoScroll() {
    // Auto-scroll to next review every 5 seconds
    setInterval(() => {
        rotateToNextReview();
    }, 5000);
}

// Scroll to the next review in sequence
function rotateToNextReview() {
    const carousel = document.getElementById('reviewsCarousel');
    if (!carousel || allReviews.length === 0) return;

    // Move to next review
    currentReviewIndex = (currentReviewIndex + 1) % allReviews.length;

    // Calculate scroll position (each card is 320px + 25px gap)
    const scrollPosition = currentReviewIndex * 345;

    // Smooth scroll to the calculated position
    carousel.scrollTo({
        left: scrollPosition,
        behavior: 'smooth'
    });
}
