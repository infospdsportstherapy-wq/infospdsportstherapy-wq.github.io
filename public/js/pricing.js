// Fetch and display pricing data
document.addEventListener('DOMContentLoaded', function() {
    const pricingRates = document.getElementById('pricingRates');
    const pricingLoading = document.getElementById('pricingLoading');
    const pricingError = document.getElementById('pricingError');

    fetch('data/pricing.json')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Clear loading message
            pricingLoading.style.display = 'none';

            // Check if pricing data exists
            if (!data.pricing || data.pricing.length === 0) {
                throw new Error('No pricing data found');
            }

            // Clear previous content
            pricingRates.innerHTML = '';

            // Render each pricing tier
            data.pricing.forEach(tier => {
                const priceDiv = document.createElement('div');
                priceDiv.innerHTML = `
                    <span class="duration">${tier.duration}</span>
                    <span class="price-sep">-</span>
                    <span class="amount">${tier.price}</span>
                `;
                pricingRates.appendChild(priceDiv);
            });
        })
        .catch(error => {
            console.error('Error loading pricing:', error);
            pricingLoading.style.display = 'none';
            pricingError.style.display = 'block';
            pricingError.textContent = 'Unable to load pricing. Please try again later.';
        });
});
