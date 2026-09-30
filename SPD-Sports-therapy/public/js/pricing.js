document.addEventListener('DOMContentLoaded', async () => {
    const target = document.querySelector('[data-pricing]');
    if (!target) return;
    try {
        const response = await fetch('data/pricing.json');
        if (!response.ok) throw new Error('Pricing unavailable');
        const data = await response.json();
        target.innerHTML = data.pricing.map(item => `
            <div class="price-row">
                <span class="duration">${item.duration}</span>
                <span class="amount">${item.price}</span>
            </div>
        `).join('');
    } catch (error) {
        target.innerHTML = '<p class="price-note">Pricing could not be loaded. Please contact SPD Sports Therapy for current rates.</p>';
    }
});
