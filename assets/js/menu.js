// Customer-facing menu JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Category accordion functionality
    const categoryHeaders = document.querySelectorAll('.menu-category h2');
    if (categoryHeaders.length > 0) {
        categoryHeaders.forEach(header => {
            header.addEventListener('click', function() {
                this.parentElement.classList.toggle('collapsed');
            });
        });
    }

    // Add animation to menu items when they come into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.menu-item').forEach(item => {
        observer.observe(item);
    });

    // Share menu functionality
    const shareBtn = document.getElementById('share-menu-btn');
    if (shareBtn && navigator.share) {
        shareBtn.style.display = 'block';
        shareBtn.addEventListener('click', async () => {
            try {
                await navigator.share({
                    title: document.title,
                    url: window.location.href
                });
            } catch (err) {
                console.log('Error sharing:', err);
            }
        });
    }
});