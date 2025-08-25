// Loading Animation
const loadingScreen = document.getElementById('loadingScreen');

// Hide loading screen after page loads
window.addEventListener('load', () => {
    setTimeout(() => {
        loadingScreen.classList.add('hidden');
        
        // Remove the loading screen from DOM after animation completes
        setTimeout(() => {
            loadingScreen.style.display = 'none';
        }, 600);
    }, 1500); // Show loading for 1.5 seconds
});

// Prevent scroll during loading
document.body.style.overflow = 'hidden';
window.addEventListener('load', () => {
    setTimeout(() => {
        document.body.style.overflow = 'auto';
    }, 1500);
});

// DOM Elements
const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
const nav = document.querySelector('.nav');
const navLinks = document.querySelectorAll('.nav-links a');
const contactForm = document.querySelector('.contact-form');
const downloadCvBtn = document.querySelector('.download-cv-btn');
const getInTouchBtn = document.querySelector('.get-in-touch-btn');

// Mobile Menu Toggle
mobileMenuToggle.addEventListener('click', () => {
    nav.classList.toggle('active');
    mobileMenuToggle.classList.toggle('active');
});

// Smooth Scrolling for Navigation Links
navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const targetId = link.getAttribute('href');
        const targetSection = document.querySelector(targetId);
        
        if (targetSection) {
            targetSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        
        // Close mobile menu if open
        nav.classList.remove('active');
        mobileMenuToggle.classList.remove('active');
    });
});

// Header Background on Scroll
window.addEventListener('scroll', () => {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
        header.style.backgroundColor = 'rgba(10, 10, 10, 0.98)';
    } else {
        header.style.backgroundColor = 'rgba(10, 10, 10, 0.95)';
    }
});

// Animated Counter for Stats
function animateCounters() {
    const counters = document.querySelectorAll('.stat .number');
    const speed = 200;

    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target') || 0;
            const count = +counter.innerText;
            const inc = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 1);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });
}

// Intersection Observer for Animations
const observerOptions = {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate');
            
            // Trigger counter animation for stats section
            if (entry.target.classList.contains('stats')) {
                animateCounters();
            }
            
            // Add staggered animation for timeline items
            if (entry.target.classList.contains('timeline')) {
                const timelineItems = entry.target.querySelectorAll('.timeline-item');
                timelineItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('animate');
                    }, index * 150);
                });
            }
            
            // Add staggered animation for project items
            if (entry.target.classList.contains('projects-grid')) {
                const projectItems = entry.target.querySelectorAll('.work-item');
                projectItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('animate');
                    }, index * 100);
                });
            }
        }
    });
}, observerOptions);

// Observe elements for animation
const animateElements = document.querySelectorAll('.section-header, .profile-card, .intro-section, .about-content, .timeline, .projects-grid, .contact-content, .works-grid');
animateElements.forEach(el => observer.observe(el));

// Contact Form Handling
contactForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const formData = new FormData(contactForm);
    const name = formData.get('name');
    const email = formData.get('email');
    const message = formData.get('message');
    
    // Simple form validation
    if (!name || !email || !message) {
        showNotification('Please fill in all fields!', 'error');
        return;
    }
    
    if (!validateEmail(email)) {
        showNotification('Please enter a valid email address!', 'error');
        return;
    }
    
    // Simulate form submission
    showNotification('Message sent successfully! I\'ll get back to you soon.', 'success');
    contactForm.reset();
});

// Email validation
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Notification styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 300px;
        font-weight: 600;
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}

// Download CV Button
downloadCvBtn.addEventListener('click', () => {
    showNotification('CV download will be available soon!', 'info');
});

// Get In Touch Button
getInTouchBtn.addEventListener('click', () => {
    document.querySelector('#contact').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
});

// Typing Animation for Hero Text
function typeWriter(element, text, speed = 100) {
    let i = 0;
    element.innerHTML = '';
    
    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }
    type();
}

// Initialize typing animation when page loads
window.addEventListener('load', () => {
    const heroTitle = document.querySelector('.intro-text h1');
    if (heroTitle) {
        const originalText = heroTitle.textContent;
        typeWriter(heroTitle, originalText, 80);
    }
});

// Parallax Effect for Hero Section (Disabled to fix hovering issue)
// window.addEventListener('scroll', () => {
//     const scrolled = window.pageYOffset;
//     const parallax = document.querySelector('.hero');
//     const speed = scrolled * 0.5;
    
//     if (parallax) {
//         parallax.style.transform = `translateY(${speed}px)`;
//     }
// });

// Work Items Hover Effect
const workItems = document.querySelectorAll('.work-item');
workItems.forEach(item => {
    item.addEventListener('mouseenter', () => {
        item.style.transform = 'translateY(-10px) scale(1.02)';
    });
    
    item.addEventListener('mouseleave', () => {
        item.style.transform = 'translateY(0) scale(1)';
    });
});

// Add CSS animations for elements
const style = document.createElement('style');
style.textContent = `
    /* Initial states for animations */
    .section-header,
    .profile-card,
    .intro-section,
    .about-content,
    .timeline,
    .projects-grid,
    .contact-content,
    .works-grid,
    .timeline-item,
    .work-item {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .timeline-item {
        transform: translateX(-50px);
    }
    
    .work-item {
        transform: translateY(30px) scale(0.95);
    }
    
    /* Animated states */
    .animate {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
    
    .timeline-item.animate {
        transform: translateX(0) !important;
    }
    
    .work-item.animate {
        transform: translateY(0) scale(1) !important;
    }
    
    /* Section header animations */
    .section-header.animate {
        animation: slideInFromTop 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    /* Profile card special animation */
    .profile-card.animate {
        animation: slideInFromLeft 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    /* Intro section special animation */
    .intro-section.animate {
        animation: slideInFromRight 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    @keyframes slideInFromTop {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideInFromLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideInFromRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    /* Hover enhancements */
    .work-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .work-item:hover {
        transform: translateY(-10px) scale(1.02) !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .timeline-item {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .timeline-item:hover {
        transform: translateX(10px) !important;
    }
    
    .nav.active {
        display: flex;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: rgba(10, 10, 10, 0.98);
        flex-direction: column;
        padding: 20px;
        gap: 20px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
    }
    
    .nav.active .nav-links {
        flex-direction: column;
        gap: 15px;
    }
    
    .mobile-menu-toggle.active span:nth-child(1) {
        transform: rotate(-45deg) translate(-5px, 6px);
    }
    
    .mobile-menu-toggle.active span:nth-child(2) {
        opacity: 0;
    }
    
    .mobile-menu-toggle.active span:nth-child(3) {
        transform: rotate(45deg) translate(-5px, -6px);
    }
    
    .work-item {
        transition: all 0.3s ease;
    }
    
    .timeline-item {
        opacity: 0;
        transform: translateX(-50px);
        transition: all 0.6s ease;
    }
    
    .timeline-item.animate {
        opacity: 1;
        transform: translateX(0);
    }
`;
document.head.appendChild(style);

// Stagger animation for timeline items
const timelineItems = document.querySelectorAll('.timeline-item');
timelineItems.forEach((item, index) => {
    setTimeout(() => {
        observer.observe(item);
    }, index * 100);
});

// Lazy loading for images
const images = document.querySelectorAll('img');
const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src || img.src;
            img.classList.add('loaded');
            imageObserver.unobserve(img);
        }
    });
});

images.forEach(img => imageObserver.observe(img));

// Update current year in footer
const currentYear = new Date().getFullYear();
const footerText = document.querySelector('.footer-content p');
if (footerText) {
    footerText.textContent = footerText.textContent.replace('2023', currentYear);
}

// Add smooth hover effects to buttons
const buttons = document.querySelectorAll('button, .hire-btn a');
buttons.forEach(button => {
    button.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px) scale(1.05)';
        this.style.boxShadow = '0 10px 20px rgba(255, 107, 53, 0.3)';
    });
    
    button.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
        this.style.boxShadow = 'none';
    });
});

console.log('Portfolio website loaded successfully! 🚀');

// Scroll Buttons Functionality
const scrollTopBtn = document.getElementById('scrollTop');
const scrollBottomBtn = document.getElementById('scrollBottom');

// Show/Hide scroll buttons based on scroll position
function toggleScrollButtons() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const documentHeight = document.documentElement.scrollHeight;
    const windowHeight = window.innerHeight;
    const scrollPercent = (scrollTop / (documentHeight - windowHeight)) * 100;

    // Show scroll to top button when scrolled down 15%
    if (scrollPercent > 15) {
        scrollTopBtn.classList.add('show');
    } else {
        scrollTopBtn.classList.remove('show');
    }

    // Show scroll to bottom button only when:
    // 1. User has scrolled down at least 10%
    // 2. And is not near the bottom (less than 85% scrolled)
    if (scrollPercent > 10 && scrollPercent < 85) {
        scrollBottomBtn.classList.add('show');
    } else {
        scrollBottomBtn.classList.remove('show');
    }
}

// Smooth scroll to top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Smooth scroll to bottom
function scrollToBottom() {
    window.scrollTo({
        top: document.documentElement.scrollHeight,
        behavior: 'smooth'
    });
}

// Event listeners for scroll buttons
scrollTopBtn.addEventListener('click', scrollToTop);
scrollBottomBtn.addEventListener('click', scrollToBottom);

// Listen for scroll events
window.addEventListener('scroll', toggleScrollButtons);

// Initial check for scroll buttons
toggleScrollButtons();

// Project Image Management
function loadProjectImage(projectId, imageUrl, projectUrl = null) {
    const projectCard = document.querySelector(`[data-project-id="${projectId}"]`);
    if (!projectCard) {
        console.error(`Project with ID ${projectId} not found`);
        return;
    }
    
    const projectImg = projectCard.querySelector('.project-img');
    const placeholder = projectCard.querySelector('.project-placeholder');
    const imageLink = projectCard.querySelector('.project-image-link');
    
    if (imageUrl && imageUrl.trim() !== '') {
        // Load the image
        projectImg.src = imageUrl;
        projectImg.onload = function() {
            // Hide placeholder and show image
            placeholder.style.display = 'none';
            projectImg.style.display = 'block';
        };
        projectImg.onerror = function() {
            // If image fails to load, show placeholder
            placeholder.style.display = 'flex';
            projectImg.style.display = 'none';
            console.error(`Failed to load image: ${imageUrl}`);
        };
    } else {
        // No image provided, show placeholder
        placeholder.style.display = 'flex';
        projectImg.style.display = 'none';
    }
    
    // Update project link if provided
    if (projectUrl && projectUrl.trim() !== '') {
        imageLink.href = projectUrl;
        imageLink.setAttribute('data-project-url', projectUrl);
    }
}

// Function to show placeholder for a project
function showProjectPlaceholder(projectId) {
    const projectCard = document.querySelector(`[data-project-id="${projectId}"]`);
    if (!projectCard) {
        console.error(`Project with ID ${projectId} not found`);
        return;
    }
    
    const projectImg = projectCard.querySelector('.project-img');
    const placeholder = projectCard.querySelector('.project-placeholder');
    
    placeholder.style.display = 'flex';
    projectImg.style.display = 'none';
    projectImg.src = '';
}

// Function to get all project data (useful for admin panel)
function getAllProjects() {
    const projects = [];
    const projectCards = document.querySelectorAll('[data-project-id]');
    
    projectCards.forEach(card => {
        const projectId = card.getAttribute('data-project-id');
        const title = card.querySelector('h3').textContent;
        const description = card.querySelector('p').textContent;
        const techTags = Array.from(card.querySelectorAll('.tech-tag')).map(tag => tag.textContent);
        const imageUrl = card.querySelector('.project-img').src;
        const projectUrl = card.querySelector('.project-image-link').getAttribute('data-project-url');
        const hasImage = card.querySelector('.project-img').style.display !== 'none';
        
        projects.push({
            id: projectId,
            title,
            description,
            technologies: techTags,
            imageUrl: hasImage ? imageUrl : null,
            projectUrl,
            hasImage
        });
    });
    
    return projects;
}

// Example usage (for testing or admin panel integration):
// loadProjectImage('1', 'path/to/portfolio-image.jpg', 'https://github.com/ripWr3ncH/Portfolio-Project');
// loadProjectImage('2', 'path/to/algorithm-visualizer.jpg');
// showProjectPlaceholder('3'); // Remove image and show placeholder

// Initialize projects on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if any project images are already set and load them
    const projectCards = document.querySelectorAll('[data-project-id]');
    projectCards.forEach(card => {
        const projectImg = card.querySelector('.project-img');
        const placeholder = card.querySelector('.project-placeholder');
        
        // If no src is set or src is empty, show placeholder
        if (!projectImg.src || projectImg.src.trim() === '' || projectImg.src.endsWith('/')) {
            placeholder.style.display = 'flex';
            projectImg.style.display = 'none';
        }
    });
});
