// GT Online Class - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            this.classList.toggle('active');
        });
    }

    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                // Close mobile menu if open
                navLinks?.classList.remove('active');
                mobileMenuToggle?.classList.remove('active');
            }
        });
    });

    // Navbar background on scroll
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);

    // Observe all animated elements
    document.querySelectorAll('.animate-fade-up, .animate-scale').forEach(el => {
        observer.observe(el);
    });

    // Admission form submission
    const admissionForm = document.getElementById('admissionForm');
    if (admissionForm) {
        admissionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            let message = 'Hello! I want to join GT Online Class.\n\nMy details:\n';
            
            for (let [key, value] of formData.entries()) {
                if (value.trim()) {
                    const label = this.querySelector(`[name="${key}"]`).previousElementSibling.textContent;
                    message += `${label}: ${value}\n`;
                }
            }
            
            const whatsappNumber = '+919876543210'; // Get from settings
            const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappURL, '_blank');
        });
    }

    // Video unlock functionality
    window.unlockVideo = function(videoId) {
        // Check if user is logged in
        <?php if (!isset($_SESSION['user_id'])): ?>
            if (confirm('You need to register/login to purchase videos. Would you like to register now?')) {
                window.location.href = 'user/register.php';
            }
            return;
        <?php else: ?>
            const upiId = '<?php echo $settings['upi_id'] ?? 'admin@paytm'; ?>';
            const amount = 99;
            
            // Create payment modal or redirect
            const paymentMessage = `Pay ₹${amount} to unlock this video.\n\nUPI ID: ${upiId}\n\nAfter payment, send screenshot to WhatsApp help for video access.`;
            
            if (confirm(paymentMessage)) {
                // You can integrate with UPI payment gateway here
                // For now, we'll show instructions
                alert('Please pay ₹99 via UPI and send payment screenshot to our WhatsApp help number. We will activate your video access within 30 minutes.');
                
                // Redirect to WhatsApp for payment confirmation
                const whatsappNumber = '<?php echo $settings['whatsapp_number'] ?? '+919876543210'; ?>';
                const whatsappMessage = `I have made payment of ₹99 for video ID: ${videoId}. Please activate my access.\n\nMy registered email: <?php echo $_SESSION['user_email'] ?? ''; ?>`;
                const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(whatsappMessage)}`;
                window.open(whatsappURL, '_blank');
            }
        <?php endif; ?>
        }

    // Testimonial modal functionality
    const testimonialModal = document.getElementById('testimonialModal');
    const testimonialForm = document.getElementById('testimonialForm');
    const testimonialReview = document.getElementById('testimonialReview');
    const characterCount = document.querySelector('.character-count');
    
    window.openTestimonialForm = function() {
        testimonialModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    // Close modal
    testimonialModal?.addEventListener('click', function(e) {
        if (e.target === this || e.target.classList.contains('close')) {
            this.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // Character counter for testimonial
    testimonialReview?.addEventListener('input', function() {
        const count = this.value.length;
        characterCount.textContent = `${count}/500`;
        
        if (count > 500) {
            characterCount.style.color = 'var(--error-color)';
        } else if (count > 450) {
            characterCount.style.color = 'var(--warning-color)';
        } else {
            characterCount.style.color = 'var(--gray-500)';
        }
    });

    // Testimonial form submission
    testimonialForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('submit_testimonial.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thank you for your testimonial! It will be reviewed and published soon.');
                testimonialModal.style.display = 'none';
                document.body.style.overflow = 'auto';
                this.reset();
                characterCount.textContent = '0/500';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });

    // Video player enhancements
    document.querySelectorAll('video').forEach(video => {
        // Add custom controls for better mobile experience
        video.addEventListener('loadedmetadata', function() {
            // Enable fullscreen on mobile
            if ('requestFullscreen' in video) {
                video.addEventListener('dblclick', function() {
                    if (!document.fullscreenElement) {
                        this.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                });
            }
        });

        // Touch controls for mobile
        let touchStartX = 0;
        let touchStartTime = 0;
        
        video.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchStartTime = Date.now();
        });
        
        video.addEventListener('touchend', function(e) {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndTime = Date.now();
            const timeDiff = touchEndTime - touchStartTime;
            const touchDiff = touchEndX - touchStartX;
            
            // Quick tap to play/pause
            if (timeDiff < 300 && Math.abs(touchDiff) < 50) {
                if (this.paused) {
                    this.play();
                } else {
                    this.pause();
                }
            }
            
            // Swipe gestures for seek (optional)
            if (timeDiff < 500 && Math.abs(touchDiff) > 100) {
                if (touchDiff > 0) {
                    // Swipe right - seek forward
                    this.currentTime = Math.min(this.duration, this.currentTime + 10);
                } else {
                    // Swipe left - seek backward
                    this.currentTime = Math.max(0, this.currentTime - 10);
                }
            }
        });
    });

    // Loading states
    function showLoading(element) {
        element.style.opacity = '0.6';
        element.style.pointerEvents = 'none';
    }
    
    function hideLoading(element) {
        element.style.opacity = '1';
        element.style.pointerEvents = 'auto';
    }

    // Form validation
    function validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--error-color)';
                isValid = false;
            } else {
                field.style.borderColor = 'var(--gray-200)';
            }
        });
        
        // Email validation
        const emailField = form.querySelector('[type="email"]');
        if (emailField && emailField.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.value)) {
                emailField.style.borderColor = 'var(--error-color)';
                isValid = false;
            }
        }
        
        // Mobile number validation
        const mobileField = form.querySelector('[type="tel"]');
        if (mobileField && mobileField.value) {
            const mobileRegex = /^[6-9]\d{9}$/;
            if (!mobileRegex.test(mobileField.value.replace(/\D/g, ''))) {
                mobileField.style.borderColor = 'var(--error-color)';
                isValid = false;
            }
        }
        
        return isValid;
    }

    // Add form validation to all forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                alert('Please fill all required fields correctly.');
            }
        });
    });

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Keyboard accessibility
    document.addEventListener('keydown', function(e) {
        // Escape key to close modals
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="block"]');
            if (openModal) {
                openModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
    });

    // Performance optimization
    let ticking = false;
    
    function updateOnScroll() {
        // Add any scroll-based animations or effects here
        ticking = false;
    }
    
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(updateOnScroll);
            ticking = true;
        }
    });

    // Service Worker registration (for PWA features)
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('SW registered: ', registration);
                })
                .catch(function(registrationError) {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }
});

// Utility functions
function formatDate(date) {
    return new Intl.DateTimeFormat('en-IN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(date));
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
}

// Export for use in other scripts
window.GTOnlineClass = {
    formatDate,
    formatCurrency,
    unlockVideo: window.unlockVideo,
    openTestimonialForm: window.openTestimonialForm
};