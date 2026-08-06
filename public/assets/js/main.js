// public/assets/js/main.js
console.log("ZeroWaste UI Loaded");

// Future: Add scroll animations or mobile menu toggles here
document.addEventListener('DOMContentLoaded', () => {
    // Parallax effect (Simple)
    const cards = document.querySelectorAll('.glass-card');
    
    document.addEventListener('mousemove', (e) => {
        const x = (window.innerWidth - e.pageX * 2) / 100;
        const y = (window.innerHeight - e.pageY * 2) / 100;
        
        cards.forEach(card => {
            // card.style.transform = `translateX(${x}px) translateY(${y}px)`;
            // Disabled for now as it can be annoying
        });
    });
});
