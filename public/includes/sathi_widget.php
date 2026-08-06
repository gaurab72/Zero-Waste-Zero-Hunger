<!-- public/includes/whatsapp_widget.php -->
<style>
    /* Floating WhatsApp Button */
    .whatsapp-float-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        border-radius: 50%;
        box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 9999;
        text-decoration: none;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
        animation: waPulse 2s infinite;
    }
    
    .whatsapp-float-btn:hover {
        transform: scale(1.12) rotate(6deg);
        box-shadow: 0 8px 30px rgba(37, 211, 102, 0.6);
    }
    
    .whatsapp-icon-svg {
        width: 34px;
        height: 34px;
        fill: #ffffff;
    }

    @keyframes waPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
        }
    }
</style>

<!-- Floating WhatsApp Action Link -->
<a href="https://wa.me/9815114901?text=Hello%20ZeroWaste-ZeroHunger%20Team,%20I%20have%20an%20inquiry." 
   target="_blank" 
   rel="noopener noreferrer" 
   class="whatsapp-float-btn" 
   aria-label="Chat with us on WhatsApp">
    <svg class="whatsapp-icon-svg" viewBox="0 0 32 32">
        <path d="M16 2A13 13 0 0 0 4.67 21.28L3 29l7.92-1.61A13 13 0 1 0 16 2zm0 23.86a10.82 10.82 0 0 1-5.52-1.5l-.4-.24-4.7 1 1-4.58-.26-.42A10.85 10.85 0 1 1 16 25.86zm5.95-8.12c-.33-.16-1.92-.95-2.22-1.06s-.52-.16-.74.16-.85 1.06-1.04 1.28-.39.24-.72.08a9.08 9.08 0 0 1-2.67-1.65 10 10 0 0 1-1.85-2.3c-.19-.33 0-.5.14-.68.16-.16.33-.39.49-.58s.22-.33.33-.55.05-.41-.03-.58-.74-1.78-1-2.44c-.27-.64-.54-.55-.74-.56h-.63a1.21 1.21 0 0 0-.88.41 3.7 3.7 0 0 0-1.15 2.75 6.44 6.44 0 0 0 1.34 3.42 14.77 14.77 0 0 0 5.65 5 18.57 18.57 0 0 0 1.88.69 4.53 4.53 0 0 0 2.08.13 3.4 3.4 0 0 0 2.23-1.57 2.77 2.77 0 0 0 .19-1.57c-.08-.13-.3-.22-.63-.38z"/>
    </svg>
</a>
